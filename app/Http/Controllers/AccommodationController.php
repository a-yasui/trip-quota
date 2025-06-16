<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TripQuota\Accommodation\AccommodationService;
use TripQuota\TravelPlan\TravelPlanService;

class AccommodationController extends Controller
{
    public function __construct(
        private AccommodationService $accommodationService,
        private TravelPlanService $travelPlanService
    ) {}

    /**
     * 宿泊施設一覧表示
     */
    public function index(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan) {
            abort(404);
        }

        try {
            $accommodations = $this->accommodationService->getAccommodationsForTravelPlan($travelPlan, Auth::user());
        } catch (\Exception $e) {
            abort(403);
        }

        return view('accommodations.index', compact('travelPlan', 'accommodations'));
    }

    /**
     * 宿泊施設作成フォーム表示
     */
    public function create(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan) {
            abort(404);
        }

        // Get confirmed members for assignment
        $members = $travelPlan->members()->where('is_confirmed', true)->get();

        return view('accommodations.create', compact('travelPlan', 'members'));
    }

    /**
     * 宿泊施設作成処理
     */
    public function store(Request $request, string $travelPlanUuid)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'price_per_night' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string|max:1000',
            'confirmation_number' => 'nullable|string|max:100',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:members,id',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan) {
                abort(404);
            }

            $this->accommodationService->createAccommodation($travelPlan, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.accommodations.index', $travelPlan->uuid)
                ->with('success', '宿泊施設を追加しました。');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 宿泊施設詳細表示
     */
    public function show(string $travelPlanUuid, Accommodation $accommodation)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan || $accommodation->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // Load relationships
        $accommodation->load(['createdBy', 'members', 'travelPlan']);

        return view('accommodations.show', compact('travelPlan', 'accommodation'));
    }

    /**
     * 宿泊施設編集フォーム表示
     */
    public function edit(string $travelPlanUuid, Accommodation $accommodation)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan || $accommodation->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // Get confirmed members for assignment
        $members = $travelPlan->members()->where('is_confirmed', true)->get();
        $accommodation->load(['members']);

        return view('accommodations.edit', compact('travelPlan', 'accommodation', 'members'));
    }

    /**
     * 宿泊施設更新処理
     */
    public function update(Request $request, string $travelPlanUuid, Accommodation $accommodation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'price_per_night' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string|max:1000',
            'confirmation_number' => 'nullable|string|max:100',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:members,id',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan || $accommodation->travel_plan_id !== $travelPlan->id) {
                abort(404);
            }

            $this->accommodationService->updateAccommodation($accommodation, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.accommodations.show', [$travelPlan->uuid, $accommodation->id])
                ->with('success', '宿泊施設を更新しました。');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 宿泊施設削除処理
     */
    public function destroy(string $travelPlanUuid, Accommodation $accommodation)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan || $accommodation->travel_plan_id !== $travelPlan->id) {
                abort(404);
            }

            $this->accommodationService->deleteAccommodation($accommodation, Auth::user());

            return redirect()
                ->route('travel-plans.accommodations.index', $travelPlan->uuid)
                ->with('success', '宿泊施設を削除しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
