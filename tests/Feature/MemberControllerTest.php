<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TravelPlan $travelPlan;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
            'creator_user_id' => $this->user->id,
            'owner_user_id' => $this->user->id,
        ]);
        // Create core group (required for member operations)
        Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'CORE',
            'name' => 'コアグループ',
        ]);

        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_index_displays_members_for_authenticated_member()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('メンバー管理');
        $response->assertSee($this->travelPlan->plan_name);
    }

    public function test_create_displays_invitation_form()
    {
        // デバッグ: メンバーが正しく設定されているか確認
        $this->assertDatabaseHas('members', [
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('メンバー招待');
        $response->assertSee('招待方法');
        $response->assertSee('メールアドレス');
        $response->assertSee('アカウント名');
    }

    public function test_store_creates_invitation_by_email()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'invitation_type' => 'email',
                'email' => 'test@example.com',
                'name' => 'Test User',
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');
    }

    public function test_store_creates_invitation_by_account_name()
    {
        $targetUser = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $targetUser->id,
            'account_name' => 'testuser123',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'invitation_type' => 'account',
                'account_name' => 'testuser123',
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), []);

        $response->assertSessionHasErrors(['invitation_type']);
    }

    public function test_show_displays_member_details()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Other Member',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.show', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertStatus(200);
        $response->assertSee($otherMember->name);
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Editable Member',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.edit', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertStatus(200);
        $response->assertSee('メンバー編集');
        $response->assertSee('value="Editable Member"', false);
    }

    public function test_update_modifies_member_with_valid_data()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Original Name',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $otherMember->id]), [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('travel-plans.members.show', [$this->travelPlan->uuid, $otherMember->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'id' => $otherMember->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_member()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.members.destroy', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('members', ['id' => $otherMember->id]);
    }

    public function test_unauthorized_user_cannot_access_members()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.members.index', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_create_invitation()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.members.create', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_user_cannot_edit_others_in_different_travel_plan()
    {
        $otherTravelPlan = TravelPlan::factory()->create();
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $otherTravelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.edit', [$otherTravelPlan->uuid, $otherMember->id]));

        $response->assertStatus(403);
    }
}
