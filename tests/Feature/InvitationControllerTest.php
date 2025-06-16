<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\GroupInvitation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\Invitation\InvitationService;

class InvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TravelPlan $travelPlan;

    private Member $member;

    private GroupInvitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'test@example.com']);
        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $this->travelPlan = TravelPlan::factory()->create();
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'is_confirmed' => true,
        ]);

        // Create core group for the travel plan
        $coreGroup = \App\Models\Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'CORE',
            'name' => 'コアグループ',
        ]);

        // Create invitation for testing
        $invitationService = app(InvitationService::class);
        $this->invitation = $invitationService->createInvitation(
            $this->travelPlan,
            $this->user,
            'invitee@example.com',
            'Test Invitee'
        );
    }

    public function test_index_displays_user_pending_invitations()
    {
        // Create a user that will receive invitations
        $inviteeUser = User::factory()->create(['email' => 'invitee@example.com']);

        $response = $this->actingAs($inviteeUser)
            ->get(route('invitations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('invitations.index');
        $response->assertViewHas('pendingInvitations');
        $response->assertSee($this->travelPlan->plan_name);
        $response->assertSee($this->member->name);
    }

    public function test_index_shows_empty_state_when_no_invitations()
    {
        $userWithoutInvitations = User::factory()->create();

        $response = $this->actingAs($userWithoutInvitations)
            ->get(route('invitations.index'));

        $response->assertStatus(200);
        $response->assertSee('招待がありません');
    }

    public function test_show_displays_invitation_details()
    {
        $inviteeUser = User::factory()->create(['email' => 'invitee@example.com']);

        $response = $this->actingAs($inviteeUser)
            ->get(route('invitations.show', $this->invitation->invitation_token));

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $response->assertViewHas('invitation');
        $response->assertSee($this->travelPlan->plan_name);
    }

    public function test_show_returns_404_for_nonexistent_invitation()
    {
        $response = $this->actingAs($this->user)
            ->get(route('invitations.show', 'nonexistent-token'));

        $response->assertStatus(404);
    }

    public function test_show_returns_404_for_wrong_user()
    {
        $wrongUser = User::factory()->create();

        $response = $this->actingAs($wrongUser)
            ->get(route('invitations.show', $this->invitation->invitation_token));

        $response->assertStatus(404);
    }

    public function test_accept_invitation_creates_member_and_redirects()
    {
        $inviteeUser = User::factory()->create(['email' => 'invitee@example.com']);

        $response = $this->actingAs($inviteeUser)
            ->post(route('invitations.accept', $this->invitation->invitation_token));

        $response->assertRedirect(route('travel-plans.show', $this->travelPlan->uuid));
        $response->assertSessionHas('success');

        // Verify invitation was accepted
        $this->invitation->refresh();
        $this->assertEquals('accepted', $this->invitation->status);
        $this->assertNotNull($this->invitation->responded_at);

        // Verify member was created
        $this->assertDatabaseHas('members', [
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $inviteeUser->id,
            'email' => 'invitee@example.com',
            'is_confirmed' => true,
        ]);
    }

    public function test_accept_invitation_fails_for_wrong_user()
    {
        $wrongUser = User::factory()->create();

        $response = $this->actingAs($wrongUser)
            ->post(route('invitations.accept', $this->invitation->invitation_token));

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Verify invitation was not accepted
        $this->invitation->refresh();
        $this->assertEquals('pending', $this->invitation->status);
    }

    public function test_accept_invitation_fails_for_expired_invitation()
    {
        // Make invitation expired
        $this->invitation->update(['expires_at' => now()->subDay()]);

        $inviteeUser = User::factory()->create(['email' => 'invitee@example.com']);

        $response = $this->actingAs($inviteeUser)
            ->post(route('invitations.accept', $this->invitation->invitation_token));

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Verify invitation was not accepted
        $this->invitation->refresh();
        $this->assertEquals('pending', $this->invitation->status);
    }

    public function test_decline_invitation_updates_status_and_redirects()
    {
        $inviteeUser = User::factory()->create(['email' => 'invitee@example.com']);

        $response = $this->actingAs($inviteeUser)
            ->post(route('invitations.decline', $this->invitation->invitation_token));

        $response->assertRedirect(route('invitations.index'));
        $response->assertSessionHas('success');

        // Verify invitation was declined
        $this->invitation->refresh();
        $this->assertEquals('declined', $this->invitation->status);
        $this->assertNotNull($this->invitation->responded_at);
    }

    public function test_decline_invitation_fails_for_wrong_user()
    {
        $wrongUser = User::factory()->create();

        $response = $this->actingAs($wrongUser)
            ->post(route('invitations.decline', $this->invitation->invitation_token));

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Verify invitation was not declined
        $this->invitation->refresh();
        $this->assertEquals('pending', $this->invitation->status);
    }

    public function test_unauthenticated_user_cannot_access_invitations()
    {
        $response = $this->get(route('invitations.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('invitations.show', $this->invitation->invitation_token));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('invitations.accept', $this->invitation->invitation_token));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('invitations.decline', $this->invitation->invitation_token));
        $response->assertRedirect(route('login'));
    }
}
