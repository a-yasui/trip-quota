<?php

namespace App\Http\Controllers;

use App\Enums\GroupType;
use App\Http\Requests\TravelPlanRequest;
use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TripQuota\TravelPlan\TravelPlanService;
use TripQuota\TravelPlan\CreateRequest;

class TravelPlanController extends Controller
{
    /**
     * @var TravelPlanService
     */
    protected TravelPlanService $travelPlanService;

    /**
     * コンストラクタ
     */
    public function __construct(TravelPlanService $travelPlanService)
    {
        $this->travelPlanService = $travelPlanService;
    }

    /**
     * Display a listing of the travel plans.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ログインユーザーが参加している旅行計画を取得
        $user = Auth::user();
        $travelPlans = TravelPlan::whereHas('groups.members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orWhere('creator_id', $user->id)
            ->orderBy('departure_date', 'asc')
            ->get();

        return view('travel-plans.index', compact('travelPlans'));
    }

    /**
     * Show the form for creating a new travel plan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('travel-plans.create');
    }

    /**
     * Store a newly created travel plan in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TravelPlanRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();

                // CreateRequestオブジェクトを作成
                $createRequest = new CreateRequest(
                    plan_name: $request->title,
                    creator: $user,
                    departure_date: new \Carbon\Carbon($request->departure_date),
                    timezone: \App\Enums\Timezone::tryFrom($request->timezone),
                    return_date: $request->return_date ? new \Carbon\Carbon($request->return_date) : null,
                    is_active: true
                );

                // TravelPlanServiceを使用して旅行計画とコアグループを作成
                $result = $this->travelPlanService->create($createRequest);
                $travelPlan = $result->plan;

                return redirect()->route('travel-plans.show', $travelPlan)
                    ->with('success', '旅行計画「'.$travelPlan->title.'」を作成しました。');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '旅行計画の作成に失敗しました。'.$e->getMessage());
        }
    }

    /**
     * Display the specified travel plan.
     *
     * @return \Illuminate\View\View
     */
    public function show(TravelPlan $travelPlan)
    {
        // コアグループを取得
        $coreGroup = $travelPlan->groups()->where('type', GroupType::CORE)->first();

        // メンバー一覧を取得
        $members = $coreGroup ? $coreGroup->members : collect();

        return view('travel-plans.show', compact('travelPlan', 'coreGroup', 'members'));
    }

    /**
     * Show the form for editing the specified travel plan.
     *
     * @return \Illuminate\View\View
     */
    public function edit(TravelPlan $travelPlan)
    {
        // 権限チェック
        $user = Auth::user();
        if ($user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        return view('travel-plans.edit', compact('travelPlan'));
    }

    /**
     * Update the specified travel plan in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TravelPlanRequest $request, TravelPlan $travelPlan)
    {
        // 権限チェック
        $user = Auth::user();
        if ($user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        try {
            DB::beginTransaction();

            // 出発日前日かどうかチェック
            $isBeforeDeparture = now()->startOfDay()->lt($travelPlan->departure_date);

            // 出発日前日の場合は全ての項目を更新可能
            if ($isBeforeDeparture) {
                $travelPlan->title = $request->title;
                $travelPlan->departure_date = $request->departure_date;
                $travelPlan->timezone = $request->timezone;

                // 帰宅日も更新
                if ($request->has('return_date')) {
                    $travelPlan->return_date = $request->return_date;
                }
            } else {
                // 出発日以降は帰宅日のみ更新可能（未記入の場合のみ）
                if ($travelPlan->return_date === null && $request->has('return_date')) {
                    $travelPlan->return_date = $request->return_date;
                }
            }

            $travelPlan->save();

            // コアグループの名前も更新（旅行計画名と同期）
            if ($isBeforeDeparture) {
                $coreGroup = $travelPlan->groups()->where('type', GroupType::CORE)->first();
                if ($coreGroup) {
                    $coreGroup->name = $travelPlan->title;
                    $coreGroup->save();
                }
            }

            DB::commit();

            return redirect()->route('travel-plans.show', $travelPlan)
                ->with('success', '旅行計画「'.$travelPlan->title.'」を更新しました。');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', '旅行計画の更新に失敗しました。'.$e->getMessage());
        }
    }

    /**
     * 旅行計画を削除
     *
     * @param  TravelPlan $travelPlan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TravelPlan $travelPlan)
    {
        // 権限チェック
        $user = Auth::user();
        if ($user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を削除する権限がありません。');
        }

        try {
            $planName = $travelPlan->title;

            // TravelPlanServiceを使用して旅行計画を削除
            $this->travelPlanService->removeTravelPlan($travelPlan);

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = 'travel_plan_deleted';
            $activityLog->subject_id = null;
            $activityLog->action = 'travel_plan_deleted';
            $activityLog->description = Auth::user()->name.'さんが旅行計画「'.$planName.'」を削除しました';
            $activityLog->save();

            return redirect()->route('travel-plans.index')
                ->with('success', '旅行計画「'.$planName.'」を削除しました。');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '旅行計画の削除に失敗しました。'.$e->getMessage());
        }
    }
}
