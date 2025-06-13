<?php

namespace TripQuota\Itinerary;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ItineraryRepository implements ItineraryRepositoryInterface
{
    public function findById(int $id): ?Itinerary
    {
        return Itinerary::with(['travelPlan', 'group', 'createdBy', 'members'])->find($id);
    }

    public function findByTravelPlan(TravelPlan $travelPlan): Collection
    {
        return Itinerary::with(['group', 'createdBy', 'members'])
            ->where('travel_plan_id', $travelPlan->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function findByTravelPlanAndGroup(TravelPlan $travelPlan, Group $group): Collection
    {
        return Itinerary::with(['createdBy', 'members'])
            ->where('travel_plan_id', $travelPlan->id)
            ->where('group_id', $group->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function findByTravelPlanAndDate(TravelPlan $travelPlan, Carbon $date): Collection
    {
        return Itinerary::with(['group', 'createdBy', 'members'])
            ->where('travel_plan_id', $travelPlan->id)
            ->whereDate('date', $date)
            ->orderBy('start_time')
            ->get();
    }

    public function findByTravelPlanDateRange(TravelPlan $travelPlan, Carbon $startDate, Carbon $endDate): Collection
    {
        return Itinerary::with(['group', 'createdBy', 'members'])
            ->where('travel_plan_id', $travelPlan->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function create(array $data): Itinerary
    {
        return Itinerary::create($data);
    }

    public function update(Itinerary $itinerary, array $data): Itinerary
    {
        $itinerary->update($data);
        return $itinerary->fresh(['travelPlan', 'group', 'createdBy', 'members']);
    }

    public function delete(Itinerary $itinerary): bool
    {
        // まずピボットテーブルの関連を削除
        $itinerary->members()->detach();
        
        return $itinerary->delete();
    }

    public function attachMembers(Itinerary $itinerary, array $memberIds): void
    {
        $itinerary->members()->attach($memberIds);
    }

    public function syncMembers(Itinerary $itinerary, array $memberIds): void
    {
        $itinerary->members()->sync($memberIds);
    }

    public function detachMembers(Itinerary $itinerary, array $memberIds): void
    {
        $itinerary->members()->detach($memberIds);
    }

    public function getMembersByItinerary(Itinerary $itinerary): Collection
    {
        return $itinerary->members()->get();
    }

    public function getItinerariesByMember(Member $member): Collection
    {
        return $member->itineraries()
            ->with(['travelPlan', 'group', 'createdBy'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }
}