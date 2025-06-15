<?php

namespace TripQuota\Group;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;

interface GroupRepositoryInterface
{
    public function create(array $data): Group;

    public function update(Group $group, array $data): Group;

    public function delete(Group $group): bool;

    public function findById(int $id): ?Group;

    public function findByTravelPlan(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findCoreGroup(TravelPlan $travelPlan): ?Group;

    public function findBranchGroups(TravelPlan $travelPlan): \Illuminate\Database\Eloquent\Collection;

    public function findByBranchKey(string $branchKey): ?Group;

    public function generateUniqueBranchKey(): string;

    public function addMemberToGroup(Group $group, Member $member): void;

    public function removeMemberFromGroup(Group $group, Member $member): void;

    public function getGroupMembers(Group $group): \Illuminate\Database\Eloquent\Collection;

    public function isGroupEmpty(Group $group): bool;
}
