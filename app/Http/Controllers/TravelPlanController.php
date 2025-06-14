<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use TripQuota\TravelPlan\TravelPlanService;

class TravelPlanController extends Controller
{
    public function __construct(
        private TravelPlanService $travelPlanService
    ) {}

    /**
     * 旅行プラン一覧表示
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        $filter = $request->get('filter', 'all'); // all, active, upcoming, past

        if ($search) {
            $travelPlans = $this->travelPlanService->searchTravelPlans($user, $search);
        } else {
            $travelPlans = match ($filter) {
                'active' => $this->travelPlanService->getActiveTravelPlans($user),
                'upcoming' => $this->travelPlanService->getUpcomingTravelPlans($user),
                'past' => $this->travelPlanService->getPastTravelPlans($user),
                default => $this->travelPlanService->getUserTravelPlans($user),
            };
        }

        return view('travel-plans.index', compact('travelPlans', 'search', 'filter'));
    }

    /**
     * 旅行プラン作成フォーム表示
     */
    public function create()
    {
        return view('travel-plans.create');
    }

    /**
     * 旅行プラン作成処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_name' => 'required|string|max:255',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'nullable|date|after:departure_date',
            'timezone' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $travelPlan = $this->travelPlanService->createTravelPlan(Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.show', $travelPlan->uuid)
                ->with('success', '旅行プランを作成しました。');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => '旅行プランの作成中にエラーが発生しました。']);
        }
    }

    /**
     * 旅行プラン詳細表示
     */
    public function show(string $uuid)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($uuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            return view('travel-plans.show', compact('travelPlan'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * 旅行プラン編集フォーム表示
     */
    public function edit(string $uuid)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($uuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            return view('travel-plans.edit', compact('travelPlan'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * 旅行プラン更新処理
     */
    public function update(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'plan_name' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'return_date' => 'nullable|date|after:departure_date',
            'timezone' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($uuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $updatedPlan = $this->travelPlanService->updateTravelPlan($travelPlan, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.show', $updatedPlan->uuid)
                ->with('success', '旅行プランを更新しました。');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => '旅行プランの更新中にエラーが発生しました。']);
        }
    }

    /**
     * 旅行プラン削除処理
     */
    public function destroy(string $uuid)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($uuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $this->travelPlanService->deleteTravelPlan($travelPlan, Auth::user());

            return redirect()
                ->route('travel-plans.index')
                ->with('success', '旅行プランを削除しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
