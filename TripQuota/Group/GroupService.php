<?php

namespace TripQuota\Group;

use App\Enums\GroupType;
use App\Models\BranchGroupConnection;
use App\Models\ExpenseSettlement;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * グループに関するドメインサービス
 */
class GroupService
{
    /**
     * メンバーをコアグループに追加する
     * コアグループが存在しない場合は新規作成する
     *
     * @param TravelPlan $trip 旅行計画
     * @param Member $member メンバー
     * @return Group コアグループ
     */
    public function addCoreMember(TravelPlan $trip, Member $member): Group
    {
        // コアグループを取得、無ければ作成
        $coreGroup = $trip->groups()->core()->first();
        if (!$coreGroup) {
            $coreGroup = new Group();
            $coreGroup->name = $trip->title . 'のメンバー';
            $coreGroup->type = GroupType::CORE;
            $coreGroup->travel_plan_id = $trip->id;
            $coreGroup->save();
        }

        // メンバーが既にコアグループに所属している場合は何もしない
        if ($member->group_id === $coreGroup->id) {
            return $coreGroup;
        }

        // メンバーのコアグループを更新
        $member->group_id = $coreGroup->id;
        $member->save();

        // 中間テーブルに保持させる
        \DB::table('group_member')
            ->insert([
                'group_id' => $coreGroup->id,
                'member_id' => $member->id,
            ]);

        return $coreGroup;
    }

    /**
     * メンバーをコアグループから削除する
     *
     * @param TravelPlan $trip 旅行計画
     * @param Member $member メンバー
     * @return Group コアグループ
     * @throws \Exception メンバーが最後の一人の場合や精算データがある場合
     */
    public function removeCoreMember(TravelPlan $trip, Member $member): Group
    {
        // コアグループを取得
        $coreGroup = $trip->groups()->core()->firstOrFail();

        // メンバーがコアグループのメンバーであることを確認
        if ($member->group_id !== $coreGroup->id) {
            throw new ModelNotFoundException('メンバーはこのコアグループに所属していません。');
        }

        // コアグループのメンバー数をカウント
        $memberCount = $coreGroup->coreMembers()->count();

        // メンバーが最後の一人の場合は削除できない
        if ($memberCount <= 1) {
            throw new \Exception('コアグループの最後のメンバーは削除できません。');
        }

        DB::transaction(function () use ($coreGroup, $member) {
            // メンバーが所属している班グループを取得して削除
            $branchGroups = $member->branchGroups;
            foreach ($branchGroups as $branchGroup) {
                $this->removeBranchMember($branchGroup, $member);
            }

            // メンバーを削除（DBからは削除せず非アクティブにする）
            $member->is_active = false;
            $member->save();
        });

        return $coreGroup;
    }

    /**
     * 班グループを作成し、メンバーを追加する
     *
     * @param TravelPlan $trip 旅行計画
     * @param string $branchGroupName 班グループ名
     * @param Member $member メンバー
     * @return Group 班グループ
     */
    public function createBranchMember(TravelPlan $trip, string $branchGroupName, Member $member): Group
    {
        // コアグループを取得
        $coreGroup = $trip->groups()->core()->firstOrFail();

        // メンバーがコアグループに所属していることを確認
        if ($member->group_id !== $coreGroup->id) {
            throw new ModelNotFoundException('メンバーはこの旅行のコアグループに所属していません。');
        }

        // 班グループを作成
        $branchGroup = new Group();
        $branchGroup->name = $branchGroupName;
        $branchGroup->type = GroupType::BRANCH;
        $branchGroup->travel_plan_id = $trip->id;
        $branchGroup->parent_group_id = $coreGroup->id;
        $branchGroup->save();

        // メンバーを班グループに追加
        $branchGroup->members()->attach($member->id);

        return $branchGroup;
    }

    /**
     * メンバーを班グループに追加する
     *
     * @param Group $group 班グループ
     * @param Member $member メンバー
     * @return Group 班グループ
     */
    public function addBranchMember(Group $group, Member $member): Group
    {
        // グループが班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            throw new \InvalidArgumentException('指定されたグループは班グループではありません。');
        }

        // コアグループを取得
        $coreGroup = $group->parentGroup;

        // メンバーがコアグループに所属していることを確認
        if ($member->group_id !== $coreGroup->id) {
            throw new ModelNotFoundException('メンバーはこの旅行のコアグループに所属していません。');
        }

        // メンバーが既に班グループに所属している場合は何もしない
        if ($group->members()->where('members.id', $member->id)->exists()) {
            return $group;
        }

        // メンバーを班グループに追加
        $group->members()->attach($member->id);

        return $group;
    }

    /**
     * メンバーを班グループから削除する
     *
     * @param Group $group 班グループ
     * @param Member $member メンバー
     * @return Group 班グループ
     * @throws \Exception 精算データがある場合
     */
    public function removeBranchMember(Group $group, Member $member): Group
    {
        // グループが班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            throw new \InvalidArgumentException('指定されたグループは班グループではありません。');
        }

        // メンバーが班グループに所属していることを確認
        if (!$group->members()->where('members.id', $member->id)->exists()) {
            throw new ModelNotFoundException('メンバーはこの班グループに所属していません。');
        }

        // 精算データの存在チェック
        $hasSettlements = ExpenseSettlement::where(function ($query) use ($member) {
            $query->where('payer_member_id', $member->id)
                  ->orWhere('receiver_member_id', $member->id);
        })->exists();

        if ($hasSettlements) {
            throw new \Exception('メンバーに精算データがあるため削除できません。');
        }

        // メンバーを班グループから削除
        $group->members()->detach($member->id);

        // 班グループにメンバーが一人もいなくなった場合は削除
        if ($group->members()->count() === 0) {
            $group->delete();
        }

        return $group;
    }
}
