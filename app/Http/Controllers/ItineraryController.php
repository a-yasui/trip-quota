<?php

namespace App\Http\Controllers;

use App\Enums\Timezone;
use App\Enums\Transportation;
use App\Models\Itinerary;
use App\Models\TravelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class ItineraryController extends Controller
{
    /**
     * Display a listing of the itineraries for a travel plan.
     *
     * @return \Illuminate\View\View
     */
    public function index(TravelPlan $travelPlan)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id) {
            abort(403, '旅行計画を閲覧する権限がありません。');
        }

        $itineraries = $travelPlan->itineraries()->orderBy('departure_time')->get();

        return view('itineraries.index', compact('travelPlan', 'itineraries'));
    }

    /**
     * Show the form for creating a new itinerary.
     *
     * @return \Illuminate\View\View
     */
    public function create(TravelPlan $travelPlan)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        // コアグループのメンバー一覧を取得
        $coreGroup = $travelPlan->groups()->where('type', \App\Enums\GroupType::CORE)->first();
        $members = $coreGroup ? $coreGroup->members : collect();

        // 班グループ（ブランチグループ）の一覧を取得
        $branchGroups = $travelPlan->groups()->where('type', \App\Enums\GroupType::BRANCH)->with('members')->get();

        // 交通手段の選択肢を取得
        $transportationTypes = array_map(function ($type) {
            return ['value' => $type->value, 'name' => $type->name];
        }, Transportation::cases());

        // タイムゾーンの選択肢を取得
        $timezones = Timezone::options();

        // 旅行計画のタイムゾーンをデフォルトとして設定
        $defaultTimezone = $travelPlan->timezone->value;

        // 旅行計画の開始日を出発時刻の初期値として設定
        $departureDate = $travelPlan->departure_date->format('Y-m-d\TH:i');

        // 翌日の日付を到着時刻の初期値として設定
        $nextDay = $travelPlan->departure_date->copy()->addDay()->format('Y-m-d\TH:i');

        return view('itineraries.create', compact(
            'travelPlan',
            'members',
            'branchGroups',
            'transportationTypes',
            'timezones',
            'defaultTimezone',
            'departureDate',
            'nextDay'
        ));
    }

    /**
     * Store a newly created itinerary in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, TravelPlan $travelPlan)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        // バリデーション
        $validated = $request->validate([
            'transportation_type' => ['required', new Enum(Transportation::class)],
            'departure_location' => 'required|string|max:255',
            'arrival_location' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'departure_timezone' => 'required|string|in:'.implode(',', Timezone::values()),
            'arrival_time' => 'required|date|after:departure_time',
            'arrival_timezone' => 'required|string|in:'.implode(',', Timezone::values()),
            'company_name' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:members,id',
        ]);

        // 飛行機の場合は会社名と便名が必須
        if ($request->transportation_type === Transportation::FLIGHT->value) {
            $request->validate([
                'company_name' => 'required|string|max:255',
                'reference_number' => 'required|string|max:255',
            ]);
        }

        try {
            DB::beginTransaction();

            // 旅程の作成
            $itinerary = new Itinerary;
            $itinerary->travel_plan_id = $travelPlan->id;
            $itinerary->transportation_type = $request->transportation_type;
            $itinerary->departure_location = $request->departure_location;
            $itinerary->arrival_location = $request->arrival_location;
            $itinerary->departure_time = $request->departure_time;
            $itinerary->departure_timezone = $request->departure_timezone;
            $itinerary->arrival_time = $request->arrival_time;
            $itinerary->arrival_timezone = $request->arrival_timezone;
            $itinerary->company_name = $request->company_name;
            $itinerary->reference_number = $request->reference_number;
            $itinerary->notes = $request->notes;
            $itinerary->save();

            // メンバーの関連付け
            if ($request->has('member_ids')) {
                $itinerary->members()->attach($request->member_ids);
            }

            DB::commit();

            return redirect()->route('travel-plans.itineraries.index', $travelPlan)
                ->with('success', '旅程を登録しました。');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', '旅程の登録に失敗しました。'.$e->getMessage());
        }
    }

    /**
     * Display the specified itinerary.
     *
     * @return \Illuminate\View\View
     */
    public function show(TravelPlan $travelPlan, Itinerary $itinerary)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id) {
            abort(403, '旅行計画を閲覧する権限がありません。');
        }

        // 旅程が指定された旅行計画に属しているか確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // メンバー情報を取得
        $itinerary->load('members');

        return view('itineraries.show', compact('travelPlan', 'itinerary'));
    }

    /**
     * Show the form for editing the specified itinerary.
     *
     * @return \Illuminate\View\View
     */
    public function edit(TravelPlan $travelPlan, Itinerary $itinerary)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        // 旅程が指定された旅行計画に属しているか確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // コアグループのメンバー一覧を取得
        $coreGroup = $travelPlan->groups()->where('type', \App\Enums\GroupType::CORE)->first();
        $members = $coreGroup ? $coreGroup->members : collect();

        // 班グループ（ブランチグループ）の一覧を取得
        $branchGroups = $travelPlan->groups()->where('type', \App\Enums\GroupType::BRANCH)->with('members')->get();

        // 現在選択されているメンバーIDのリストを取得
        $selectedMemberIds = $itinerary->members->pluck('id')->toArray();

        // 交通手段の選択肢を取得
        $transportationTypes = array_map(function ($type) {
            return ['value' => $type->value, 'name' => $type->name];
        }, Transportation::cases());

        // タイムゾーンの選択肢を取得
        $timezones = Timezone::options();

        // 旅行計画のタイムゾーンをデフォルトとして設定
        $defaultTimezone = $travelPlan->timezone->value;

        return view('itineraries.edit', compact(
            'travelPlan',
            'itinerary',
            'members',
            'branchGroups',
            'selectedMemberIds',
            'transportationTypes',
            'timezones',
            'defaultTimezone'
        ));
    }

    /**
     * Update the specified itinerary in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TravelPlan $travelPlan, Itinerary $itinerary)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        // 旅程が指定された旅行計画に属しているか確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // バリデーション
        $validated = $request->validate([
            'transportation_type' => ['required', new Enum(Transportation::class)],
            'departure_location' => 'required|string|max:255',
            'arrival_location' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'departure_timezone' => 'required|string|in:'.implode(',', Timezone::values()),
            'arrival_time' => 'required|date|after:departure_time',
            'arrival_timezone' => 'required|string|in:'.implode(',', Timezone::values()),
            'company_name' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:members,id',
        ]);

        // 飛行機の場合は会社名と便名が必須
        if ($request->transportation_type === Transportation::FLIGHT->value) {
            $request->validate([
                'company_name' => 'required|string|max:255',
                'reference_number' => 'required|string|max:255',
            ]);
        }

        try {
            DB::beginTransaction();

            // 旅程の更新
            $itinerary->transportation_type = $request->transportation_type;
            $itinerary->departure_location = $request->departure_location;
            $itinerary->arrival_location = $request->arrival_location;
            $itinerary->departure_time = $request->departure_time;
            $itinerary->departure_timezone = $request->departure_timezone;
            $itinerary->arrival_time = $request->arrival_time;
            $itinerary->arrival_timezone = $request->arrival_timezone;
            $itinerary->company_name = $request->company_name;
            $itinerary->reference_number = $request->reference_number;
            $itinerary->notes = $request->notes;
            $itinerary->save();

            // メンバーの関連付けを更新
            if ($request->has('member_ids')) {
                $itinerary->members()->sync($request->member_ids);
            } else {
                $itinerary->members()->detach();
            }

            DB::commit();

            return redirect()->route('travel-plans.itineraries.index', $travelPlan)
                ->with('success', '旅程を更新しました。');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', '旅程の更新に失敗しました。'.$e->getMessage());
        }
    }

    /**
     * Remove the specified itinerary from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TravelPlan $travelPlan, Itinerary $itinerary)
    {
        // 権限チェック
        $user = Auth::user();
        $isMember = $travelPlan->groups()->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        if (! $isMember && $user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
            abort(403, '旅行計画を編集する権限がありません。');
        }

        // 旅程が指定された旅行計画に属しているか確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        try {
            DB::beginTransaction();

            // メンバーの関連付けを削除
            $itinerary->members()->detach();

            // 旅程を削除
            $itinerary->delete();

            DB::commit();

            return redirect()->route('travel-plans.itineraries.index', $travelPlan)
                ->with('success', '旅程を削除しました。');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', '旅程の削除に失敗しました。'.$e->getMessage());
        }
    }
}
