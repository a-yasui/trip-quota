<?php

namespace TripQuota\User;

use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use App\Models\ExpenseSettlement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use TripQuota\Group\GroupService;

/**
 * ユーザーとメンバーに関するドメインサービス
 */
class UserService
{
    /**
     * メンバーを作成する
     *
     * @param TravelPlan $plan 旅行計画
     * @param string $name メンバー名
     * @param string $email メールアドレス（オプション）
     * @return Member 作成されたメンバー
     */
    public function createMember(TravelPlan $plan, string $name, string $email = ''): Member
    {
        // コアグループを取得
        $coreGroup = $plan->groups()->core()->firstOrFail();

        // 既にメンバーが存在するか確認
        $existingMember = null;
        if (!empty($email)) {
            $existingMember = Member::where('email', $email)
                ->where('group_id', $coreGroup->id)
                ->first();
            
            if ($existingMember) {
                return $existingMember;
            }
        }

        // メールアドレスからユーザーを検索
        $user = null;
        if (!empty($email)) {
            $user = User::where('email', $email)->first();
        }

        // メンバーを作成
        $member = new Member();
        $member->name = $name;
        $member->email = $email;
        $member->group_id = $coreGroup->id;
        $member->is_active = true;
        
        // ユーザーが存在する場合は関連付け
        if ($user) {
            $member->user_id = $user->id;
        }
        
        $member->save();

        // コアグループに追加
        $groupService = new GroupService();
        $groupService->addCoreMember($plan, $member);

        return $member;
    }

    /**
     * メンバーを削除する
     *
     * @param TravelPlan $plan 旅行計画
     * @param Member $member メンバー
     * @throws \Exception 精算データがある場合
     */
    public function removeMember(TravelPlan $plan, Member $member): void
    {
        // コアグループを取得
        $coreGroup = $plan->groups()->core()->firstOrFail();

        // メンバーがこの旅行計画のメンバーであることを確認
        if ($member->group_id !== $coreGroup->id) {
            throw new ModelNotFoundException('メンバーはこの旅行計画に所属していません。');
        }

        // 精算データの存在チェック
        $hasSettlements = ExpenseSettlement::where(function ($query) use ($member) {
            $query->where('payer_member_id', $member->id)
                  ->orWhere('receiver_member_id', $member->id);
        })->exists();

        if ($hasSettlements) {
            throw new \Exception('メンバーに精算データがあるため削除できません。');
        }

        DB::transaction(function () use ($plan, $member) {
            // 班グループからの削除処理
            $branchGroups = $member->branchGroups;
            $groupService = new GroupService();
            
            foreach ($branchGroups as $branchGroup) {
                // 班グループに所属している場合は削除
                if ($branchGroup->members()->where('members.id', $member->id)->exists()) {
                    $groupService->removeBranchMember($branchGroup, $member);
                }
            }
            
            // コアグループからメンバーを削除
            $groupService->removeCoreMember($plan, $member);
        });
    }
} 