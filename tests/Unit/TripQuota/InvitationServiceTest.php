<?php

namespace Tests\Unit\TripQuota;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use TripQuota\Group\GroupRepositoryInterface;
use TripQuota\Invitation\InvitationRepositoryInterface;
use TripQuota\Invitation\InvitationService;
use TripQuota\Member\MemberRepositoryInterface;

class InvitationServiceTest extends TestCase
{
    private InvitationService $service;

    private InvitationRepositoryInterface $invitationRepository;

    private GroupRepositoryInterface $groupRepository;

    private MemberRepositoryInterface $memberRepository;

    private TravelPlan $travelPlan;

    private User $user;

    private Member $member;

    private Group $coreGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invitationRepository = $this->createMock(InvitationRepositoryInterface::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->memberRepository = $this->createMock(MemberRepositoryInterface::class);

        $this->service = new InvitationService(
            $this->invitationRepository,
            $this->groupRepository,
            $this->memberRepository
        );

        // Test data
        $this->travelPlan = new TravelPlan;
        $this->travelPlan->id = 1;

        $this->user = new User;
        $this->user->id = 1;
        $this->user->email = 'test@example.com';

        $this->member = new Member;
        $this->member->id = 1;
        $this->member->is_confirmed = true;

        $this->coreGroup = new Group;
        $this->coreGroup->id = 1;
        $this->coreGroup->type = 'CORE';
    }

    public function test_create_invitation_creates_invitation_successfully()
    {
        $email = 'invitee@example.com';
        $name = 'Test Invitee';
        $token = 'unique-token-123';

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->groupRepository
            ->expects($this->once())
            ->method('findCoreGroup')
            ->with($this->travelPlan)
            ->willReturn($this->coreGroup);

        $this->invitationRepository
            ->expects($this->once())
            ->method('generateUniqueToken')
            ->willReturn($token);

        $expectedData = [
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->coreGroup->id,
            'invited_by_member_id' => $this->member->id,
            'invitee_email' => $email,
            'invitee_name' => $name,
            'invitation_token' => $token,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(7),
        ];

        $invitation = new GroupInvitation;
        $this->invitationRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($data) use ($expectedData) {
                // Check all fields except expires_at which is a Carbon instance
                foreach ($expectedData as $key => $value) {
                    if ($key === 'expires_at') {
                        continue;
                    }
                    if ($data[$key] !== $value) {
                        return false;
                    }
                }

                return $data['expires_at'] instanceof Carbon;
            }))
            ->willReturn($invitation);

        $result = $this->service->createInvitation($this->travelPlan, $this->user, $email, $name);

        $this->assertSame($invitation, $result);
    }

    public function test_create_invitation_fails_when_inviter_not_member()
    {
        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('招待を送信する権限がありません。');

        $this->service->createInvitation($this->travelPlan, $this->user, 'test@example.com');
    }

    public function test_create_invitation_fails_when_inviter_not_confirmed()
    {
        $this->member->is_confirmed = false;

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('招待を送信する権限がありません。');

        $this->service->createInvitation($this->travelPlan, $this->user, 'test@example.com');
    }

    public function test_create_invitation_fails_when_core_group_not_found()
    {
        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->groupRepository
            ->expects($this->once())
            ->method('findCoreGroup')
            ->with($this->travelPlan)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('コアグループが見つかりません。');

        $this->service->createInvitation($this->travelPlan, $this->user, 'test@example.com');
    }

    public function test_accept_invitation_successfully()
    {
        $token = 'valid-token';
        $inviteeUser = new User;
        $inviteeUser->id = 2;
        $inviteeUser->email = 'invitee@example.com';

        $invitation = new GroupInvitation;
        $invitation->status = 'pending';
        $invitation->expires_at = Carbon::now()->addDays(1);
        $invitation->invitee_email = 'invitee@example.com';
        $invitation->travel_plan_id = $this->travelPlan->id;
        $invitation->group_id = $this->coreGroup->id;
        $invitation->invitee_name = 'Test Invitee';
        $invitation->travelPlan = $this->travelPlan;

        $this->invitationRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with($token)
            ->willReturn($invitation);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndEmail')
            ->with($this->travelPlan, 'invitee@example.com')
            ->willReturn(null);

        $newMember = new Member;
        $newMember->travelPlan = $this->travelPlan;

        $this->memberRepository
            ->expects($this->once())
            ->method('create')
            ->with([
                'travel_plan_id' => $this->travelPlan->id,
                'user_id' => $inviteeUser->id,
                'account_id' => null,
                'name' => 'Test Invitee',
                'email' => 'invitee@example.com',
                'is_confirmed' => true,
            ])
            ->willReturn($newMember);

        // グループ関連付けのモック設定
        $this->groupRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->coreGroup->id)
            ->willReturn($this->coreGroup);

        $this->groupRepository
            ->expects($this->once())
            ->method('addMemberToGroup')
            ->with($this->coreGroup, $newMember);

        $this->invitationRepository
            ->expects($this->once())
            ->method('update')
            ->with($invitation, $this->callback(function ($data) {
                return $data['status'] === 'accepted' && isset($data['responded_at']);
            }));

        $result = $this->service->acceptInvitation($token, $inviteeUser);

        $this->assertSame($newMember, $result);
    }

    public function test_accept_invitation_fails_when_invitation_not_found()
    {
        $this->invitationRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with('invalid-token')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('招待が見つかりません。');

        $this->service->acceptInvitation('invalid-token', $this->user);
    }

    public function test_accept_invitation_fails_when_already_processed()
    {
        $invitation = new GroupInvitation;
        $invitation->status = 'accepted';

        $this->invitationRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with('token')
            ->willReturn($invitation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('この招待は既に処理済みです。');

        $this->service->acceptInvitation('token', $this->user);
    }

    public function test_accept_invitation_fails_when_expired()
    {
        $invitation = new GroupInvitation;
        $invitation->status = 'pending';
        $invitation->expires_at = Carbon::now()->subDay();

        $this->invitationRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with('token')
            ->willReturn($invitation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('この招待は期限切れです。');

        $this->service->acceptInvitation('token', $this->user);
    }

    public function test_accept_invitation_fails_when_wrong_email()
    {
        $invitation = new GroupInvitation;
        $invitation->status = 'pending';
        $invitation->expires_at = Carbon::now()->addDay();
        $invitation->invitee_email = 'other@example.com';

        $this->invitationRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with('token')
            ->willReturn($invitation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('この招待はあなた宛ではありません。');

        $this->service->acceptInvitation('token', $this->user);
    }

    public function test_decline_invitation_successfully()
    {
        $token = 'valid-token';
        $inviteeUser = new User;
        $inviteeUser->email = 'invitee@example.com';

        $invitation = new GroupInvitation;
        $invitation->status = 'pending';
        $invitation->invitee_email = 'invitee@example.com';
        $invitation->travelPlan = $this->travelPlan;

        $this->invitationRepository
            ->expects($this->once())
            ->method('findByToken')
            ->with($token)
            ->willReturn($invitation);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndEmail')
            ->with($this->travelPlan, 'invitee@example.com')
            ->willReturn(null);

        $updatedInvitation = new GroupInvitation;
        $this->invitationRepository
            ->expects($this->once())
            ->method('update')
            ->with($invitation, $this->callback(function ($data) {
                return $data['status'] === 'declined' && isset($data['responded_at']);
            }))
            ->willReturn($updatedInvitation);

        $result = $this->service->declineInvitation($token, $inviteeUser);

        $this->assertSame($updatedInvitation, $result);
    }

    public function test_get_user_pending_invitations()
    {
        $invitations = \Illuminate\Database\Eloquent\Collection::make([new GroupInvitation]);

        $this->invitationRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->user->email)
            ->willReturn($invitations);

        $result = $this->service->getUserPendingInvitations($this->user);

        $this->assertSame($invitations, $result);
    }

    public function test_cancel_invitation_successfully()
    {
        $invitation = new GroupInvitation;
        $invitation->status = 'pending';
        $invitation->travelPlan = $this->travelPlan;
        $invitation->invitee_email = 'invitee@example.com';

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndEmail')
            ->with($this->travelPlan, 'invitee@example.com')
            ->willReturn(null);

        $this->invitationRepository
            ->expects($this->once())
            ->method('delete')
            ->with($invitation)
            ->willReturn(true);

        $result = $this->service->cancelInvitation($invitation, $this->user);

        $this->assertTrue($result);
    }

    public function test_cancel_invitation_fails_when_not_member()
    {
        $invitation = new GroupInvitation;
        $invitation->travelPlan = $this->travelPlan;

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('この招待をキャンセルする権限がありません。');

        $this->service->cancelInvitation($invitation, $this->user);
    }
}
