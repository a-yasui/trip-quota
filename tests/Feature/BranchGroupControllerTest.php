<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class BranchGroupControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_soft_deletes_members_when_branch_group_is_deleted()
    {
        // Arrange
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['creator_id' => $user->id]);
        $branchGroup = Group::factory()->create(['type' => 'branch', 'travel_plan_id' => $travelPlan->id]);
        $member = Member::factory()->create(['group_id' => $branchGroup->id]);

        // Act
        $this->actingAs($user)->delete(route('branch-groups.destroy', $branchGroup));

        // Assert
        $this->assertNull(Group::find($branchGroup->id));
    }

    /**
     * @test
     */
    public function it_duplicates_members_when_branch_group_is_duplicated()
    {
        // Arrange
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create(['creator_id' => $user->id]);
        $branchGroup = Group::factory()->create(['type' => 'branch', 'travel_plan_id' => $travelPlan->id]);
        $member = Member::factory()->create(['group_id' => $branchGroup->id]);
        $branchGroup->branchMembers()->attach([$member->id]);

        // Act
        $this->actingAs($user)->post(route('branch-groups.store-duplicate', $branchGroup), [
            'name' => 'Duplicated Group',
            'description' => 'Duplicated description',
        ])->assertSessionHasNoErrors();

        // デバッグ出力を追加
        $duplicatedGroup = Group::where('name', 'Duplicated Group')->first();
        $this->assertNotNull($duplicatedGroup);

        // Assert
        $this->assertDatabaseHas('group_member', [
            'member_id' => $member->id,
            'group_id' => $duplicatedGroup->id,
        ]);
    }
}
