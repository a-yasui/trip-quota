<?php

namespace TripQuota\Member;

use App\Models\Account;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use TripQuota\Invitation\InvitationService;

class MemberService
{
    public function __construct(
        private MemberRepositoryInterface $memberRepository,
        private InvitationService $invitationService
    ) {}

    public function inviteMemberByEmail(TravelPlan $travelPlan, User $inviter, string $email, ?string $name = null): void
    {
        $this->ensureUserCanInviteMembers($travelPlan, $inviter);
        $this->ensureEmailNotAlreadyMember($travelPlan, $email);

        DB::transaction(function () use ($travelPlan, $inviter, $email, $name) {
            // メンバーレコードを作成（未確認状態）
            $member = $this->memberRepository->create([
                'travel_plan_id' => $travelPlan->id,
                'user_id' => null, // 未登録ユーザー
                'account_id' => null,
                'name' => $name ?? $email,
                'email' => $email,
                'is_confirmed' => false,
            ]);

            // 招待を送信
            $this->invitationService->createInvitation($travelPlan, $inviter, $email, $name);
        });
    }

    public function inviteMemberByAccountName(TravelPlan $travelPlan, User $inviter, string $accountName): void
    {
        $this->ensureUserCanInviteMembers($travelPlan, $inviter);

        $account = Account::findByAccountNameIgnoreCase($accountName);
        if (! $account) {
            throw new \Exception('指定されたアカウント名が見つかりません。');
        }

        $this->ensureUserNotAlreadyMember($travelPlan, $account->user);

        DB::transaction(function () use ($travelPlan, $inviter, $account) {
            // メンバーレコードを作成（未確認状態）
            $member = $this->memberRepository->create([
                'travel_plan_id' => $travelPlan->id,
                'user_id' => $account->user_id,
                'account_id' => $account->id,
                'name' => $account->display_name,
                'email' => $account->user->email,
                'is_confirmed' => false,
            ]);

            // 招待を送信
            $this->invitationService->createInvitation($travelPlan, $inviter, $account->user->email, $account->display_name);
        });
    }

    public function confirmMembership(Member $member, User $user): Member
    {
        if ($member->user_id !== $user->id) {
            throw new \Exception('この招待を確認する権限がありません。');
        }

        if ($member->is_confirmed) {
            throw new \Exception('既に確認済みです。');
        }

        return $this->memberRepository->update($member, [
            'is_confirmed' => true,
        ]);
    }

    public function removeMember(Member $member, User $remover): bool
    {
        $this->ensureUserCanRemoveMembers($member->travelPlan, $remover);
        $this->ensureMemberCanBeRemoved($member);

        return DB::transaction(function () use ($member) {
            // 関連データの削除
            // TODO: 費用、宿泊施設、行程などからのメンバー削除

            return $this->memberRepository->delete($member);
        });
    }

    public function updateMember(Member $member, User $updater, array $data): Member
    {
        $this->ensureUserCanUpdateMember($member, $updater);

        return $this->memberRepository->update($member, [
            'name' => $data['name'] ?? $member->name,
            'account_id' => $data['account_id'] ?? $member->account_id,
        ]);
    }

    public function getMembersForTravelPlan(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewMembers($travelPlan, $user);

        return $this->memberRepository->findByTravelPlan($travelPlan);
    }

    public function findMemberByTravelPlanAndUser(TravelPlan $travelPlan, User $user): ?Member
    {
        return $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);
    }

    public function getConfirmedMembers(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewMembers($travelPlan, $user);

        return $this->memberRepository->findConfirmedByTravelPlan($travelPlan);
    }

    public function getUnconfirmedMembers(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewMembers($travelPlan, $user);

        return $this->memberRepository->findUnconfirmedByTravelPlan($travelPlan);
    }

    public function getMemberCount(TravelPlan $travelPlan): int
    {
        return $this->memberRepository->countByTravelPlan($travelPlan);
    }

    private function ensureUserCanViewMembers(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランのメンバーを表示する権限がありません。');
        }
    }

    private function ensureUserCanInviteMembers(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('メンバーを招待する権限がありません。');
        }
    }

    private function ensureUserCanRemoveMembers(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('メンバーを削除する権限がありません。');
        }
    }

    private function ensureUserCanUpdateMember(Member $member, User $user): void
    {
        // 自分自身の情報は更新可能
        if ($member->user_id === $user->id) {
            return;
        }

        // 他のメンバーの確認済みメンバーは他のメンバーの情報を更新可能
        $requesterMember = $this->memberRepository->findByTravelPlanAndUser($member->travelPlan, $user);
        if (! $requesterMember || ! $requesterMember->is_confirmed) {
            throw new \Exception('このメンバーの情報を更新する権限がありません。');
        }
    }

    private function ensureEmailNotAlreadyMember(TravelPlan $travelPlan, string $email): void
    {
        $existingMember = $this->memberRepository->findByTravelPlanAndEmail($travelPlan, $email);

        if ($existingMember) {
            throw new \Exception('このメールアドレスは既にメンバーに追加されています。');
        }
    }

    private function ensureUserNotAlreadyMember(TravelPlan $travelPlan, User $user): void
    {
        $existingMember = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if ($existingMember) {
            throw new \Exception('このユーザーは既にメンバーに追加されています。');
        }
    }

    private function ensureMemberCanBeRemoved(Member $member): void
    {
        // 旅行プランの所有者は削除できない
        if ($member->travelPlan->owner_user_id === $member->user_id) {
            throw new \Exception('旅行プランの所有者は削除できません。');
        }
    }
}
