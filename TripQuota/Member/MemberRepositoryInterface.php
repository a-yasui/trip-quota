<?php

namespace TripQuota\Member;

use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;

interface MemberRepositoryInterface
{
    public function create(array $data): Member;

    public function update(Member $member, array $data): Member;

    public function delete(Member $member): bool;

    public function findById(int $id): ?Member;

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findByTravelPlanAndUser(TravelPlan $travelPlan, User $user): ?Member;

    public function findByTravelPlanAndEmail(TravelPlan $travelPlan, string $email): ?Member;

    public function findUnconfirmedByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findConfirmedByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function countByTravelPlan(TravelPlan $travelPlan): int;
}
