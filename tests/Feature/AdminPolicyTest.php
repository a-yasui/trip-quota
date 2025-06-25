<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\TravelPlan;
use App\Models\Member;
use App\Policies\TravelPlanPolicy;
use App\Policies\MemberPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_full_access_to_travel_plan_policy(): void
    {
        $admin = Admin::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $policy = new TravelPlanPolicy();

        // Test all TravelPlanPolicy methods return true for Admin
        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $travelPlan));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $travelPlan));
        $this->assertTrue($policy->delete($admin, $travelPlan));
        $this->assertTrue($policy->restore($admin, $travelPlan));
        $this->assertTrue($policy->forceDelete($admin, $travelPlan));
        $this->assertTrue($policy->inviteMembers($admin, $travelPlan));
        $this->assertTrue($policy->manageGroups($admin, $travelPlan));
        $this->assertTrue($policy->manageExpenses($admin, $travelPlan));
        $this->assertTrue($policy->manageAccommodations($admin, $travelPlan));
        $this->assertTrue($policy->manageItineraries($admin, $travelPlan));
        $this->assertTrue($policy->transferOwnership($admin, $travelPlan));
        $this->assertTrue($policy->confirmMembers($admin, $travelPlan));
    }

    public function test_admin_has_full_access_to_member_policy(): void
    {
        $admin = Admin::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $member = Member::factory()->forTravelPlan($travelPlan)->create();
        $policy = new MemberPolicy();

        // Test all MemberPolicy methods return true for Admin
        $this->assertTrue($policy->viewAnyForTravelPlan($admin, $travelPlan));
        $this->assertTrue($policy->view($admin, $member));
        $this->assertTrue($policy->invite($admin, $travelPlan));
        $this->assertTrue($policy->update($admin, $member));
        $this->assertTrue($policy->delete($admin, $member));
    }
}