<?php

namespace TripQuota\TravelPlan;

use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface TravelPlanRepositoryInterface
{
    public function create(array $data): TravelPlan;

    public function update(TravelPlan $travelPlan, array $data): TravelPlan;

    public function delete(TravelPlan $travelPlan): bool;

    public function findById(int $id): ?TravelPlan;

    public function findByUuid(string $uuid): ?TravelPlan;

    public function findByUser(User $user, int $perPage = 15): LengthAwarePaginator;

    public function findActiveByUser(User $user, int $perPage = 15): LengthAwarePaginator;

    public function findUpcoming(User $user, int $perPage = 15): LengthAwarePaginator;

    public function findPast(User $user, int $perPage = 15): LengthAwarePaginator;

    public function searchByName(User $user, string $searchTerm, int $perPage = 15): LengthAwarePaginator;
}
