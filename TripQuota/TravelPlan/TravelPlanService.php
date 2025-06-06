<?php

namespace TripQuota\TravelPlan;

use App\Enums\GroupType;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\ExpenseSettlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TripQuota\Group\GroupService;
use TripQuota\User\UserService;

/**
 * 旅行計画に関するドメインサービス
 */
class TravelPlanService
{
    /**
     * 旅行計画を作成する
     *
     * @param CreateRequest $request 旅行計画作成リクエスト
     * @return GroupCreateResult 作成された旅行計画とコアグループ
     */
    public function create(CreateRequest $request): GroupCreateResult
    {
        return DB::transaction(function () use ($request) {
            // 旅行計画を作成
            $plan = new TravelPlan();
            $plan->title = $request->plan_name;
            $plan->creator_id = $request->creator->id;
            $plan->deletion_permission_holder_id = $request->creator->id;
            $plan->departure_date = $request->departure_date;
            $plan->timezone = $request->timezone;
            $plan->return_date = $request->return_date;
            $plan->is_active = $request->is_active;
            $plan->save();

            // コアグループを作成
            $coreGroup = new Group();
            $coreGroup->name = $request->plan_name . 'のメンバー';
            $coreGroup->type = GroupType::CORE;
            $coreGroup->travel_plan_id = $plan->id;
            $coreGroup->save();

            // UserService を利用して作成者をメンバーとして追加
            $userService = new UserService();
            $userService->createMember($plan, $request->creator->name, $request->creator->email);

            return new GroupCreateResult($plan, $coreGroup);
        });
    }

    /**
     * 旅行計画に対して班グループを作成する
     *
     * @param TravelPlan $plan 旅行計画
     * @param string $branch_name 班グループ名
     * @return Group 作成された班グループ
     */
    public function addBranchGroup(TravelPlan $plan, string $branch_name): Group
    {
        // コアグループの存在確認
        $coreGroup = $plan->groups()->core()->firstOrFail();

        // 班グループを作成
        $branchGroup = new Group();
        $branchGroup->name = $branch_name;
        $branchGroup->type = GroupType::BRANCH;
        $branchGroup->travel_plan_id = $plan->id;
        $branchGroup->parent_group_id = $coreGroup->id;
        $branchGroup->save();

        return $branchGroup;
    }

    /**
     * 班グループを削除する
     *
     * @param Group $group 班グループ
     * @throws \Exception 精算情報がある場合やコアグループの場合
     */
    public function removeBranchGroup(Group $group): void
    {
        // グループが班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            throw new \InvalidArgumentException('指定されたグループはコアグループであるため削除できません。');
        }

        // 精算情報の存在チェック
        $branchMembers = $group->members()->pluck('members.id')->toArray();

        if (count($branchMembers) > 0) {
            $hasSettlements = ExpenseSettlement::where(function ($query) use ($branchMembers) {
                $query->whereIn('payer_member_id', $branchMembers)
                      ->orWhereIn('receiver_member_id', $branchMembers);
            })->exists();

            if ($hasSettlements) {
                throw new \Exception('グループのメンバーに精算データがあるため削除できません。');
            }
        }

        // 関連データの削除
        DB::transaction(function () use ($group) {
            // メンバー関連をデタッチ
            $group->members()->detach();

            // 班グループ接続を削除
            $group->sourceBranchGroupConnections()->delete();
            $group->targetBranchGroupConnections()->delete();

            // システム班グループキーを削除
            if ($group->systemBranchGroupKey) {
                $group->systemBranchGroupKey->delete();
            }

            // グループを削除
            $group->delete();
        });
    }

    /**
     * 旅行計画を削除する
     *
     * @param TravelPlan $plan 旅行計画
     */
    public function removeTravelPlan(TravelPlan $plan): void
    {
        DB::transaction(function () use ($plan) {
            // 関連するすべてのグループを取得
            $groups = $plan->groups;

            foreach ($groups as $group) {
                // コアグループのメンバーを非アクティブに
                if ($group->type === GroupType::CORE) {
                    foreach ($group->coreMembers as $member) {
                        $member->is_active = false;
                        $member->save();
                    }
                }

                // 班グループのメンバー関連をデタッチ
                if ($group->type === GroupType::BRANCH) {
                    $group->members()->detach();

                    // 班グループ接続を削除
                    $group->sourceBranchGroupConnections()->delete();
                    $group->targetBranchGroupConnections()->delete();

                    // システム班グループキーを削除
                    if ($group->systemBranchGroupKey) {
                        $group->systemBranchGroupKey->delete();
                    }
                }

                // グループを削除
                $group->delete();
            }

            // 旅行計画を削除
            $plan->delete();
        });
    }
}
