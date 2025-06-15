<?php

namespace TripQuota\Group;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use TripQuota\Member\MemberRepositoryInterface;

class GroupService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private MemberRepositoryInterface $memberRepository
    ) {}

    public function createBranchGroup(TravelPlan $travelPlan, User $user, array $data): Group
    {
        $this->ensureUserCanManageGroups($travelPlan, $user);

        return DB::transaction(function () use ($travelPlan, $data) {
            $branchKey = $this->groupRepository->generateUniqueBranchKey();

            return $this->groupRepository->create([
                'travel_plan_id' => $travelPlan->id,
                'type' => 'BRANCH',
                'name' => $data['name'],
                'branch_key' => $branchKey,
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function updateGroup(Group $group, User $user, array $data): Group
    {
        $this->ensureUserCanManageGroups($group->travelPlan, $user);
        $this->ensureGroupCanBeEdited($group);

        return $this->groupRepository->update($group, [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function deleteGroup(Group $group, User $user): bool
    {
        $this->ensureUserCanManageGroups($group->travelPlan, $user);
        $this->ensureGroupCanBeDeleted($group);

        return DB::transaction(function () use ($group) {
            // グループに関連するデータの削除
            // TODO: 宿泊施設、行程、費用などの関連データの処理

            return $this->groupRepository->delete($group);
        });
    }

    public function getGroupsForTravelPlan(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewGroups($travelPlan, $user);

        return $this->groupRepository->findByTravelPlan($travelPlan);
    }

    public function getCoreGroup(TravelPlan $travelPlan, User $user): ?Group
    {
        $this->ensureUserCanViewGroups($travelPlan, $user);

        return $this->groupRepository->findCoreGroup($travelPlan);
    }

    public function getBranchGroups(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewGroups($travelPlan, $user);

        return $this->groupRepository->findBranchGroups($travelPlan);
    }

    public function findGroupByBranchKey(string $branchKey): ?Group
    {
        return $this->groupRepository->findByBranchKey($branchKey);
    }

    private function ensureUserCanViewGroups(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランのグループを表示する権限がありません。');
        }
    }

    private function ensureUserCanManageGroups(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('グループを管理する権限がありません。');
        }
    }

    private function ensureGroupCanBeEdited(Group $group): void
    {
        if ($group->type === 'CORE') {
            throw new \Exception('コアグループは編集できません。');
        }
    }

    public function addMemberToGroup(Group $group, Member $member, User $user): void
    {
        $this->ensureUserCanManageGroups($group->travelPlan, $user);
        $this->ensureMemberCanBeAddedToGroup($group, $member);

        $this->groupRepository->addMemberToGroup($group, $member);
    }

    public function removeMemberFromGroup(Group $group, Member $member, User $user): void
    {
        $this->ensureUserCanManageGroups($group->travelPlan, $user);
        $this->ensureMemberCanBeRemovedFromGroup($group, $member);

        $this->groupRepository->removeMemberFromGroup($group, $member);
    }

    public function getGroupMembers(Group $group, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewGroups($group->travelPlan, $user);

        return $this->groupRepository->getGroupMembers($group);
    }

    public function duplicateGroupMembers(Group $sourceGroup, Group $targetGroup, User $user): void
    {
        $this->ensureUserCanManageGroups($sourceGroup->travelPlan, $user);
        $this->ensureUserCanManageGroups($targetGroup->travelPlan, $user);

        $members = $this->groupRepository->getGroupMembers($sourceGroup);

        foreach ($members as $member) {
            $this->groupRepository->addMemberToGroup($targetGroup, $member);
        }
    }

    private function ensureGroupCanBeDeleted(Group $group): void
    {
        if ($group->type === 'CORE') {
            throw new \Exception('コアグループは削除できません。');
        }

        // メンバーが存在する場合は削除不可
        if (!$this->groupRepository->isGroupEmpty($group)) {
            throw new \Exception('メンバーが所属しているグループは削除できません。');
        }
    }

    private function ensureMemberCanBeAddedToGroup(Group $group, Member $member): void
    {
        if ($group->travel_plan_id !== $member->travel_plan_id) {
            throw new \Exception('異なる旅行プランのメンバーをグループに追加することはできません。');
        }

        if (!$member->is_confirmed) {
            throw new \Exception('未確認のメンバーをグループに追加することはできません。');
        }
    }

    private function ensureMemberCanBeRemovedFromGroup(Group $group, Member $member): void
    {
        if ($group->type === 'CORE') {
            throw new \Exception('コアグループからメンバーを削除することはできません。');
        }
    }
}
