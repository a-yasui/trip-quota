<?php

namespace TripQuota\Member;

use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;

class MemberRepository implements MemberRepositoryInterface
{
    public function create(array $data): Member
    {
        return Member::create($data);
    }

    public function update(Member $member, array $data): Member
    {
        $member->update($data);

        return $member->fresh();
    }

    public function delete(Member $member): bool
    {
        return $member->delete();
    }

    public function findById(int $id): ?Member
    {
        return Member::find($id);
    }

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return Member::where('travel_plan_id', $travelPlan->id)
            ->with(['user', 'account'])
            ->orderBy('is_confirmed', 'desc')
            ->orderBy('created_at')
            ->get();
    }

    public function findByTravelPlanAndUser(TravelPlan $travelPlan, User $user): ?Member
    {
        return Member::where('travel_plan_id', $travelPlan->id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function findByTravelPlanAndEmail(TravelPlan $travelPlan, string $email): ?Member
    {
        return Member::where('travel_plan_id', $travelPlan->id)
            ->where('email', $email)
            ->first();
    }

    public function findUnconfirmedByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return Member::where('travel_plan_id', $travelPlan->id)
            ->where('is_confirmed', false)
            ->with(['user', 'account'])
            ->orderBy('created_at')
            ->get();
    }

    public function findConfirmedByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return Member::where('travel_plan_id', $travelPlan->id)
            ->where('is_confirmed', true)
            ->with(['user', 'account'])
            ->orderBy('created_at')
            ->get();
    }

    public function countByTravelPlan(TravelPlan $travelPlan): int
    {
        return Member::where('travel_plan_id', $travelPlan->id)->count();
    }
}
