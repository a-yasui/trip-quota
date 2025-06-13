<?php

namespace TripQuota\Itinerary;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface ItineraryRepositoryInterface
{
    public function findById(int $id): ?Itinerary;

    public function findByTravelPlan(TravelPlan $travelPlan): Collection;

    public function findByTravelPlanAndGroup(TravelPlan $travelPlan, Group $group): Collection;

    public function findByTravelPlanAndDate(TravelPlan $travelPlan, Carbon $date): Collection;

    public function findByTravelPlanDateRange(TravelPlan $travelPlan, Carbon $startDate, Carbon $endDate): Collection;

    public function create(array $data): Itinerary;

    public function update(Itinerary $itinerary, array $data): Itinerary;

    public function delete(Itinerary $itinerary): bool;

    public function attachMembers(Itinerary $itinerary, array $memberIds): void;

    public function syncMembers(Itinerary $itinerary, array $memberIds): void;

    public function detachMembers(Itinerary $itinerary, array $memberIds): void;

    public function getMembersByItinerary(Itinerary $itinerary): Collection;

    public function getItinerariesByMember(Member $member): Collection;
}