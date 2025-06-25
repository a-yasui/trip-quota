<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TravelPlan;
use App\Models\Member;

class TravelPlanPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test viewAny policy
     */
    public function test_authenticated_user_can_view_any_travel_plans(): void
    {
        $user = User::factory()->create();
        
        $this->assertTrue($user->can('viewAny', TravelPlan::class));
    }

    /**
     * Test view policy - member can view
     */
    public function test_member_can_view_travel_plan(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
        ]);
        
        $this->assertTrue($user->can('view', $travelPlan));
    }

    /**
     * Test view policy - non-member cannot view
     */
    public function test_non_member_cannot_view_travel_plan(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        
        $this->assertFalse($user->can('view', $travelPlan));
    }

    /**
     * Test create policy
     */
    public function test_authenticated_user_can_create_travel_plan(): void
    {
        $user = User::factory()->create();
        
        $this->assertTrue($user->can('create', TravelPlan::class));
    }

    /**
     * Test update policy - confirmed member can update
     */
    public function test_confirmed_member_can_update_travel_plan(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => true,
        ]);
        
        $this->assertTrue($user->can('update', $travelPlan));
    }

    /**
     * Test update policy - unconfirmed member cannot update
     */
    public function test_unconfirmed_member_cannot_update_travel_plan(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => false,
        ]);
        
        $this->assertFalse($user->can('update', $travelPlan));
    }

    /**
     * Test delete policy - owner can delete
     */
    public function test_owner_can_delete_travel_plan(): void
    {
        $owner = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['owner_user_id' => $owner->id]);
        
        $this->assertTrue($owner->can('delete', $travelPlan));
    }

    /**
     * Test delete policy - non-owner cannot delete
     */
    public function test_non_owner_cannot_delete_travel_plan(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['owner_user_id' => $owner->id]);
        
        $this->assertFalse($user->can('delete', $travelPlan));
    }

    /**
     * Test inviteMembers policy - confirmed member can invite
     */
    public function test_confirmed_member_can_invite_members(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => true,
        ]);
        
        $this->assertTrue($user->can('inviteMembers', $travelPlan));
    }

    /**
     * Test inviteMembers policy - unconfirmed member cannot invite
     */
    public function test_unconfirmed_member_cannot_invite_members(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => false,
        ]);
        
        $this->assertFalse($user->can('inviteMembers', $travelPlan));
    }

    /**
     * Test manageGroups policy
     */
    public function test_confirmed_member_can_manage_groups(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => true,
        ]);
        
        $this->assertTrue($user->can('manageGroups', $travelPlan));
    }

    /**
     * Test manageExpenses policy
     */
    public function test_confirmed_member_can_manage_expenses(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => true,
        ]);
        
        $this->assertTrue($user->can('manageExpenses', $travelPlan));
    }

    /**
     * Test manageAccommodations policy
     */
    public function test_confirmed_member_can_manage_accommodations(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => true,
        ]);
        
        $this->assertTrue($user->can('manageAccommodations', $travelPlan));
    }

    /**
     * Test manageItineraries policy
     */
    public function test_confirmed_member_can_manage_itineraries(): void
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->create([
            'user_id' => $user->id,
            'travel_plan_id' => $travelPlan->id,
            'is_confirmed' => true,
        ]);
        
        $this->assertTrue($user->can('manageItineraries', $travelPlan));
    }

    /**
     * Test transferOwnership policy - owner can transfer
     */
    public function test_owner_can_transfer_ownership(): void
    {
        $owner = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['owner_user_id' => $owner->id]);
        
        $this->assertTrue($owner->can('transferOwnership', $travelPlan));
    }

    /**
     * Test transferOwnership policy - non-owner cannot transfer
     */
    public function test_non_owner_cannot_transfer_ownership(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['owner_user_id' => $owner->id]);
        
        $this->assertFalse($user->can('transferOwnership', $travelPlan));
    }

    /**
     * Test confirmMembers policy - creator can confirm
     */
    public function test_creator_can_confirm_members(): void
    {
        $creator = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['creator_user_id' => $creator->id]);
        
        $this->assertTrue($creator->can('confirmMembers', $travelPlan));
    }

    /**
     * Test confirmMembers policy - non-creator cannot confirm
     */
    public function test_non_creator_cannot_confirm_members(): void
    {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['creator_user_id' => $creator->id]);
        
        $this->assertFalse($user->can('confirmMembers', $travelPlan));
    }

    /**
     * Test restore policy - owner can restore
     */
    public function test_owner_can_restore_travel_plan(): void
    {
        $owner = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['owner_user_id' => $owner->id]);
        
        $this->assertTrue($owner->can('restore', $travelPlan));
    }

    /**
     * Test forceDelete policy - owner can force delete
     */
    public function test_owner_can_force_delete_travel_plan(): void
    {
        $owner = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['owner_user_id' => $owner->id]);
        
        $this->assertTrue($owner->can('forceDelete', $travelPlan));
    }
}
