<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItineraryRequest;
use App\Models\Group;
use App\Models\Itinerary;
use App\Models\TravelPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use TripQuota\Itinerary\ItineraryService;

class ItineraryController extends Controller
{
    public function __construct(
        private ItineraryService $itineraryService
    ) {}

    public function index(Request $request, string $travelPlanUuid)
    {
        $travelPlan = TravelPlan::where('uuid', $travelPlanUuid)->firstOrFail();

        try {
            // フィルター条件の取得
            $groupId = $request->input('group_id');
            $date = $request->input('date');

            if ($groupId) {
                $group = Group::findOrFail($groupId);
                $itineraries = $this->itineraryService->getItinerariesByGroup($travelPlan, $group, Auth::user());
            } elseif ($date) {
                $dateObj = Carbon::parse($date);
                $itineraries = $this->itineraryService->getItinerariesByDate($travelPlan, $dateObj, Auth::user());
            } else {
                $itineraries = $this->itineraryService->getItinerariesByTravelPlan($travelPlan, Auth::user());
            }

            // グループ一覧取得（フィルター用）
            $groups = $travelPlan->groups()->orderBy('type')->orderBy('name')->get();

            return view('itineraries.index', compact('travelPlan', 'itineraries', 'groups'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('travel-plans.show', $travelPlan->uuid)
                ->withErrors($e->errors());
        }
    }

    public function create(Request $request, string $travelPlanUuid)
    {
        $travelPlan = TravelPlan::where('uuid', $travelPlanUuid)->firstOrFail();

        try {
            // ユーザーの権限チェック（createItineraryメソッド内で実行）
            $this->itineraryService->getItinerariesByTravelPlan($travelPlan, Auth::user());

            // グループ一覧とメンバー一覧を取得
            $groups = $travelPlan->groups()->orderBy('type')->orderBy('name')->get();
            $members = $travelPlan->members()->where('is_confirmed', true)->orderBy('name')->get();

            // デフォルト値の設定
            $defaultDate = $request->input('date', $travelPlan->departure_date->format('Y-m-d'));
            $defaultGroupId = $request->input('group_id');

            return view('itineraries.create', compact('travelPlan', 'groups', 'members', 'defaultDate', 'defaultGroupId'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('travel-plans.show', $travelPlan->uuid)
                ->withErrors($e->errors());
        }
    }

    public function store(ItineraryRequest $request, string $uuid)
    {
        $travelPlan = TravelPlan::where('uuid', $uuid)->firstOrFail();
        $validatedData = $request->validated();

        try {
            $itinerary = $this->itineraryService->createItinerary($travelPlan, Auth::user(), $validatedData);

            return redirect()
                ->route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id])
                ->with('success', '旅程を作成しました。');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }

    public function show(string $travelPlanUuid, Itinerary $itinerary)
    {
        $travelPlan = TravelPlan::where('uuid', $travelPlanUuid)->firstOrFail();

        // 旅程が旅行プランに属していることを確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        try {
            // 表示権限チェック
            $this->itineraryService->getItinerariesByTravelPlan($travelPlan, Auth::user());

            $itinerary->load(['group', 'createdBy', 'members']);

            return view('itineraries.show', compact('travelPlan', 'itinerary'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('travel-plans.show', $travelPlan->uuid)
                ->withErrors($e->errors());
        }
    }

    public function edit(string $travelPlanUuid, Itinerary $itinerary)
    {
        $travelPlan = TravelPlan::where('uuid', $travelPlanUuid)->firstOrFail();

        // 旅程が旅行プランに属していることを確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        try {
            // 編集権限チェック（updateItineraryメソッド内で実行）
            $this->itineraryService->getItinerariesByTravelPlan($travelPlan, Auth::user());

            // グループ一覧とメンバー一覧を取得
            $groups = $travelPlan->groups()->orderBy('group_type')->orderBy('name')->get();
            $members = $travelPlan->members()->where('is_confirmed', true)->orderBy('name')->get();

            $itinerary->load(['group', 'createdBy', 'members']);

            return view('itineraries.edit', compact('travelPlan', 'itinerary', 'groups', 'members'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id])
                ->withErrors($e->errors());
        }
    }

    public function update(ItineraryRequest $request, string $uuid, Itinerary $itinerary)
    {
        $travelPlan = TravelPlan::where('uuid', $uuid)->firstOrFail();

        // 旅程が旅行プランに属していることを確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        $validatedData = $request->validated();

        try {
            $updatedItinerary = $this->itineraryService->updateItinerary($itinerary, Auth::user(), $validatedData);

            return redirect()
                ->route('travel-plans.itineraries.show', [$travelPlan->uuid, $updatedItinerary->id])
                ->with('success', '旅程を更新しました。');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }

    public function destroy(string $travelPlanUuid, Itinerary $itinerary)
    {
        $travelPlan = TravelPlan::where('uuid', $travelPlanUuid)->firstOrFail();

        // 旅程が旅行プランに属していることを確認
        if ($itinerary->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        try {
            $this->itineraryService->deleteItinerary($itinerary, Auth::user());

            return redirect()
                ->route('travel-plans.itineraries.index', $travelPlan->uuid)
                ->with('success', '旅程を削除しました。');
        } catch (ValidationException $e) {
            return redirect()
                ->route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id])
                ->withErrors($e->errors());
        }
    }

    /**
     * タイムライン表示
     */
    public function timeline(Request $request, string $travelPlanUuid)
    {
        $travelPlan = TravelPlan::where('uuid', $travelPlanUuid)->firstOrFail();

        try {
            // 日付範囲の設定（デフォルトは旅行期間）
            $startDate = $request->input('start_date')
                ? Carbon::parse($request->input('start_date'))
                : $travelPlan->departure_date;

            $endDate = $request->input('end_date')
                ? Carbon::parse($request->input('end_date'))
                : ($travelPlan->return_date ?? $travelPlan->departure_date->addDays(7));

            $itineraries = $this->itineraryService->getItinerariesByDateRange($travelPlan, $startDate, $endDate, Auth::user());

            // 日付ごとにグループ化
            $itinerariesByDate = $itineraries->groupBy(function ($itinerary) {
                return $itinerary->date->format('Y-m-d');
            });

            return view('itineraries.timeline', compact('travelPlan', 'itinerariesByDate', 'startDate', 'endDate'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('travel-plans.show', $travelPlan->uuid)
                ->withErrors($e->errors());
        }
    }
}
