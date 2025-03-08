<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_retrieves_branch_groups_correctly()
    {
        // Arrange: Create a travel plan and groups
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => \App\Enums\GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $branchGroup1 = Group::factory()->create([
            'type' => \App\Enums\GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $branchGroup2 = Group::factory()->create([
            'type' => \App\Enums\GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
        ]);

        // Act: Retrieve branch groups
        $branchGroups = Group::branch()->get();

        // Assert: Check if the retrieved groups are correct
        $this->assertCount(2, $branchGroups);
        $this->assertTrue($branchGroups->contains($branchGroup1));
        $this->assertTrue($branchGroups->contains($branchGroup2));
    }

    /** @test */
    public function it_retrieves_members_in_branch_groups_correctly()
    {
        // Arrange: Create a travel plan and groups
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => \App\Enums\GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);

        // Create members
        $memberA = Member::factory()->create(['group_id' => $coreGroup->id]);
        $memberB = Member::factory()->create(['group_id' => $coreGroup->id]);
        $memberC = Member::factory()->create(['group_id' => $coreGroup->id]);

        // Create branch groups and assign members
        $branchGroup1 = Group::factory()->create([
            'type' => \App\Enums\GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $branchGroup1->members()->attach([$memberA->id, $memberB->id]);

        $branchGroup2 = Group::factory()->create([
            'type' => \App\Enums\GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $branchGroup2->members()->attach([$memberA->id, $memberC->id]);

        // Act: Retrieve branch groups with members
        $branchGroups = $travelPlan->groups()->branch()->with(['members' => function($query) {
            $query->select('members.id', 'members.name', 'members.email', 'members.group_id');
        }])->get();

        // Assert: Check if the members in branch groups are correct
        $this->assertCount(2, $branchGroups);
        $this->assertEqualsCanonicalizing([
            $memberA->id, $memberB->id
        ], $branchGroups->firstWhere('id', $branchGroup1->id)->members->pluck('id')->all());
        $this->assertEqualsCanonicalizing([
            $memberA->id, $memberC->id
        ], $branchGroups->firstWhere('id', $branchGroup2->id)->members->pluck('id')->all());
    }
} 