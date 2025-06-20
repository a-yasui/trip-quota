<?php

namespace TripQuota\Member;

use App\Models\Account;
use App\Models\Member;
use App\Models\MemberLinkRequest;
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
            'email' => array_key_exists('email', $data) ? $data['email'] : $member->email,
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

    /**
     * 表示名のみでメンバーを追加（招待なし）
     */
    public function createMemberByNameOnly(TravelPlan $travelPlan, User $creator, string $name): Member
    {
        $this->ensureUserCanInviteMembers($travelPlan, $creator);

        return DB::transaction(function () use ($travelPlan, $name) {
            return $this->memberRepository->create([
                'travel_plan_id' => $travelPlan->id,
                'user_id' => null,
                'account_id' => null,
                'name' => $name,
                'email' => null,
                'is_confirmed' => true, // 表示名のみメンバーは即座に確認済み
            ]);
        });
    }

    /**
     * メンバーの関連付けリクエストを作成
     */
    public function createLinkRequest(Member $member, User $requester, ?string $targetEmail = null, ?string $targetAccountName = null): MemberLinkRequest
    {
        $this->ensureUserCanInviteMembers($member->travelPlan, $requester);

        // 対象ユーザーを特定
        $targetUser = null;

        if ($targetEmail) {
            $targetUser = User::where('email', $targetEmail)->first();
        } elseif ($targetAccountName) {
            $account = Account::findByAccountNameIgnoreCase($targetAccountName);
            $targetUser = $account?->user;
        }

        if (! $targetUser) {
            throw new \Exception('指定されたユーザーが見つかりません。');
        }

        // 既に関連付けされているかチェック
        if ($member->user_id === $targetUser->id) {
            throw new \Exception('このメンバーは既に指定されたユーザーと関連付けされています。');
        }

        // 既存の関連付けリクエストをチェック
        $existingRequest = MemberLinkRequest::where('member_id', $member->id)
            ->where('target_user_id', $targetUser->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            throw new \Exception('このユーザーに対する関連付けリクエストが既に送信されています。');
        }

        return MemberLinkRequest::create([
            'member_id' => $member->id,
            'requested_by_user_id' => $requester->id,
            'target_user_id' => $targetUser->id,
            'target_email' => $targetEmail,
            'target_account_name' => $targetAccountName,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * 関連付けリクエストを承認
     */
    public function approveLinkRequest(MemberLinkRequest $linkRequest, User $user): Member
    {
        if ($linkRequest->target_user_id !== $user->id) {
            throw new \Exception('この関連付けリクエストを承認する権限がありません。');
        }

        if (! $linkRequest->isPending()) {
            throw new \Exception('この関連付けリクエストは既に処理されているか期限切れです。');
        }

        return DB::transaction(function () use ($linkRequest, $user) {
            // Race condition防止のため、関連付けリクエストとメンバーを排他ロック
            $lockedLinkRequest = MemberLinkRequest::lockForUpdate()
                ->where('id', $linkRequest->id)
                ->first();

            if (! $lockedLinkRequest) {
                throw new \Exception('関連付けリクエストが見つかりません。');
            }

            // ロック後に再度状態をチェック（他のプロセスで既に処理された可能性）
            if (! $lockedLinkRequest->isPending()) {
                throw new \Exception('この関連付けリクエストは既に処理されているか期限切れです。');
            }

            // メンバーも排他ロックで取得
            $lockedMember = Member::lockForUpdate()
                ->where('id', $lockedLinkRequest->member_id)
                ->first();

            if (! $lockedMember) {
                throw new \Exception('関連付け対象のメンバーが見つかりません。');
            }

            // ロック後に再度チェック（他のプロセスで既に関連付けられた可能性）
            if ($lockedMember->user_id) {
                throw new \Exception('このメンバーは既に他のユーザーと関連付けられています。');
            }

            // ユーザーの適切なアカウントを取得（優先順位: 指定されたアカウント名 > デフォルトアカウント > 最初のアカウント）
            $account = null;
            if ($lockedLinkRequest->target_account_name) {
                $account = $user->accounts()->where('account_name', $lockedLinkRequest->target_account_name)->first();
            }
            if (! $account) {
                $account = $user->accounts()->first();
            }

            // メンバーを更新
            $updatedMember = $this->memberRepository->update($lockedMember, [
                'user_id' => $user->id,
                'account_id' => $account?->id,
                'email' => $user->email,
                'is_confirmed' => true,
            ]);

            // 関連付けリクエストを承認状態に更新
            $lockedLinkRequest->approve();

            return $updatedMember;
        });
    }

    /**
     * メンバーの確認状態を変更（旅行プラン作成者のみ）
     */
    public function confirmMember(Member $member, User $user): Member
    {
        $travelPlan = $member->travelPlan;

        // 旅行プラン作成者のみ変更可能
        if ($travelPlan->creator_user_id !== $user->id) {
            throw new \Exception('メンバーの状態を変更する権限がありません。');
        }

        return $this->memberRepository->update($member, [
            'is_confirmed' => true,
        ]);
    }

    /**
     * 関連付けリクエストを拒否
     */
    public function declineLinkRequest(MemberLinkRequest $linkRequest, User $user): MemberLinkRequest
    {
        if ($linkRequest->target_user_id !== $user->id) {
            throw new \Exception('この関連付けリクエストを拒否する権限がありません。');
        }

        if (! $linkRequest->isPending()) {
            throw new \Exception('この関連付けリクエストは既に処理されているか期限切れです。');
        }

        return DB::transaction(function () use ($linkRequest) {
            // Race condition防止のため、関連付けリクエストを排他ロック
            $lockedLinkRequest = MemberLinkRequest::lockForUpdate()
                ->where('id', $linkRequest->id)
                ->first();

            if (! $lockedLinkRequest) {
                throw new \Exception('関連付けリクエストが見つかりません。');
            }

            // ロック後に再度状態をチェック（他のプロセスで既に処理された可能性）
            if (! $lockedLinkRequest->isPending()) {
                throw new \Exception('この関連付けリクエストは既に処理されているか期限切れです。');
            }

            $lockedLinkRequest->decline();

            return $lockedLinkRequest;
        });
    }

    /**
     * ユーザー宛ての関連付けリクエスト一覧を取得
     */
    public function getPendingLinkRequestsForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return MemberLinkRequest::pending()
            ->forUser($user)
            ->with(['member.travelPlan', 'requestedByUser'])
            ->get();
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
        // 旅行プラン作成者は常に削除可能
        if ($travelPlan->creator_user_id === $user->id) {
            return;
        }

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
