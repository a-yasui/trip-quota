<?php

namespace TripQuota\Accommodation;

use App\Models\Accommodation;
use App\Models\TravelPlan;

class AccommodationRepository implements AccommodationRepositoryInterface
{
    public function create(array $data): Accommodation
    {
        return Accommodation::create($data);
    }

    public function update(Accommodation $accommodation, array $data): Accommodation
    {
        $accommodation->update($data);

        return $accommodation->fresh();
    }

    public function delete(Accommodation $accommodation): bool
    {
        return $accommodation->delete();
    }

    public function findById(int $id): ?Accommodation
    {
        return Accommodation::with(['travelPlan', 'createdBy', 'members'])->find($id);
    }

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return Accommodation::where('travel_plan_id', $travelPlan->id)
            ->with(['createdBy', 'members'])
            ->orderBy('check_in_date')
            ->orderBy('created_at')
            ->get();
    }

    public function findByDateRange(TravelPlan $travelPlan, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Accommodation::where('travel_plan_id', $travelPlan->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_date', [$startDate, $endDate])
                    ->orWhereBetween('check_out_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('check_in_date', '<=', $startDate)
                            ->where('check_out_date', '>=', $endDate);
                    });
            })
            ->with(['createdBy', 'members'])
            ->orderBy('check_in_date')
            ->get();
    }
}
