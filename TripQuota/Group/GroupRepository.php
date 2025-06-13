<?php

namespace TripQuota\Group;

use App\Models\Group;
use App\Models\TravelPlan;
use Illuminate\Support\Str;

class GroupRepository implements GroupRepositoryInterface
{
    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(Group $group, array $data): Group
    {
        $group->update($data);

        return $group->fresh();
    }

    public function delete(Group $group): bool
    {
        return $group->delete();
    }

    public function findById(int $id): ?Group
    {
        return Group::find($id);
    }

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return Group::where('travel_plan_id', $travelPlan->id)
            ->orderBy('type')
            ->orderBy('created_at')
            ->get();
    }

    public function findCoreGroup(TravelPlan $travelPlan): ?Group
    {
        return Group::where('travel_plan_id', $travelPlan->id)
            ->where('type', 'CORE')
            ->first();
    }

    public function findBranchGroups(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection
    {
        return Group::where('travel_plan_id', $travelPlan->id)
            ->where('type', 'BRANCH')
            ->orderBy('created_at')
            ->get();
    }

    public function findByBranchKey(string $branchKey): ?Group
    {
        return Group::where('branch_key', $branchKey)->first();
    }

    public function generateUniqueBranchKey(): string
    {
        do {
            $key = 'branch_'.Str::random(8);
        } while (Group::where('branch_key', $key)->exists());

        return $key;
    }
}
