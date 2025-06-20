<?php

namespace TripQuota\Accommodation;

use App\Models\Accommodation;
use App\Models\TravelPlan;

interface AccommodationRepositoryInterface
{
    public function create(array $data): Accommodation;

    public function update(Accommodation $accommodation, array $data): Accommodation;

    public function delete(Accommodation $accommodation): bool;

    public function findById(int $id): ?Accommodation;

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findByDateRange(TravelPlan $travelPlan, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection;
}
