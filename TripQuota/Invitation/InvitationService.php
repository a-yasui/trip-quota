<?php

namespace TripQuota\Invitation;

use App\Models\GroupInvitation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TripQuota\Group\GroupRepositoryInterface;
use TripQuota\Member\MemberRepositoryInterface;

class InvitationService
{
    public function __construct(
        private InvitationRepositoryInterface $invitationRepository,
        private GroupRepositoryInterface $groupRepository,
        private MemberRepositoryInterface $memberRepository
    ) {}

    public function findByToken(string $token): ?GroupInvitation
    {
        return $this->invitationRepository->findByToken($token);
    }

    public function createInvitation(TravelPlan $travelPlan, User $inviter, string $email, ?string $name = null): GroupInvitation
    {
        $inviterMember = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $inviter);

        if (! $inviterMember || ! $inviterMember->is_confirmed) {
            throw new \Exception('招待を送信する権限がありません。');
        }

        // コアグループに招待
        $coreGroup = $this->groupRepository->findCoreGroup($travelPlan);
        if (! $coreGroup) {
            throw new \Exception('コアグループが見つかりません。');
        }

        return $this->invitationRepository->create([
            'travel_plan_id' => $travelPlan->id,
            'group_id' => $coreGroup->id,
            'invited_by_member_id' => $inviterMember->id,
            'invitee_email' => $email,
            'invitee_name' => $name,
            'invitation_token' => $this->invitationRepository->generateUniqueToken(),
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(7), // 7日間有効
        ]);
    }

    public function acceptInvitation(string $token, User $user): Member
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if (! $invitation) {
            throw new \Exception('招待が見つかりません。');
        }

        if ($invitation->status !== 'pending') {
            throw new \Exception('この招待は既に処理済みです。');
        }

        if ($invitation->expires_at->isPast()) {
            throw new \Exception('この招待は期限切れです。');
        }

        if ($invitation->invitee_email !== $user->email) {
            throw new \Exception('この招待はあなた宛ではありません。');
        }

        return DB::transaction(function () use ($invitation, $user) {
            // 既存のメンバーレコードを探す
            $member = $this->memberRepository->findByTravelPlanAndEmail($invitation->travelPlan, $user->email);

            if ($member) {
                // 既存のメンバーレコードを更新
                $member = $this->memberRepository->update($member, [
                    'user_id' => $user->id,
                    'is_confirmed' => true,
                ]);
            } else {
                // 新しいメンバーレコードを作成
                $member = $this->memberRepository->create([
                    'travel_plan_id' => $invitation->travel_plan_id,
                    'user_id' => $user->id,
                    'account_id' => null, // TODO: ユーザーのデフォルトアカウントを設定
                    'name' => $invitation->invitee_name ?? $user->email,
                    'email' => $user->email,
                    'is_confirmed' => true,
                ]);
            }

            // メンバーを招待先のグループに関連付け
            if ($invitation->group_id) {
                $group = $this->groupRepository->findById($invitation->group_id);
                if ($group) {
                    $this->groupRepository->addMemberToGroup($group, $member);
                }
            }

            // 招待を受諾状態に更新
            $this->invitationRepository->update($invitation, [
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            return $member;
        });
    }

    public function declineInvitation(string $token, User $user): GroupInvitation
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if (! $invitation) {
            throw new \Exception('招待が見つかりません。');
        }

        if ($invitation->status !== 'pending') {
            throw new \Exception('この招待は既に処理済みです。');
        }

        if ($invitation->invitee_email !== $user->email) {
            throw new \Exception('この招待はあなた宛ではありません。');
        }

        return DB::transaction(function () use ($invitation) {
            // 招待を拒否状態に更新
            $updatedInvitation = $this->invitationRepository->update($invitation, [
                'status' => 'declined',
                'responded_at' => now(),
            ]);

            // 未確認のメンバーレコードがあれば削除
            $member = $this->memberRepository->findByTravelPlanAndEmail($invitation->travelPlan, $invitation->invitee_email);
            if ($member && ! $member->is_confirmed) {
                $this->memberRepository->delete($member);
            }

            return $updatedInvitation;
        });
    }

    public function getInvitationsForTravelPlan(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランの招待を表示する権限がありません。');
        }

        return $this->invitationRepository->findByTravelPlan($travelPlan);
    }

    public function getPendingInvitationsForTravelPlan(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランの招待を表示する権限がありません。');
        }

        return $this->invitationRepository->findPendingByTravelPlan($travelPlan);
    }

    public function getUserPendingInvitations(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->invitationRepository->findByEmail($user->email);
    }

    public function cancelInvitation(GroupInvitation $invitation, User $user): bool
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($invitation->travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('この招待をキャンセルする権限がありません。');
        }

        if ($invitation->status !== 'pending') {
            throw new \Exception('この招待は既に処理済みです。');
        }

        return DB::transaction(function () use ($invitation) {
            // 未確認のメンバーレコードがあれば削除
            $member = $this->memberRepository->findByTravelPlanAndEmail($invitation->travelPlan, $invitation->invitee_email);
            if ($member && ! $member->is_confirmed) {
                $this->memberRepository->delete($member);
            }

            // 招待を削除
            return $this->invitationRepository->delete($invitation);
        });
    }

    public function cleanupExpiredInvitations(): int
    {
        return $this->invitationRepository->deleteExpiredInvitations();
    }
}
