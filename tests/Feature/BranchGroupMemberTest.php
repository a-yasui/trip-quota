<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchGroupMemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 班グループにメンバーを追加できるかテスト
     */
    public function test_user_can_add_member_to_branch_group()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'branch',
            'parent_group_id' => $coreGroup->id,
            'name' => 'テスト班グループ',
        ]);

        // コアグループにメンバーを追加
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        // 別のメンバーをコアグループに追加
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'name' => 'テストメンバー2',
        ]);

        // 最初のメンバーを班グループに追加
        Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $member1->name,
            'email' => $member1->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        // 2人目のメンバーを班グループに追加
        $response = $this->actingAs($user)
            ->post(route('branch-groups.members.store', $branchGroup), [
                'member_id' => $member2->id,
            ]);

        $response->assertRedirect(route('branch-groups.show', $branchGroup));
        $this->assertDatabaseHas('members', [
            'name' => 'テストメンバー2',
            'group_id' => $branchGroup->id,
        ]);
    }

    /**
     * 班グループからメンバーを削除できるかテスト
     */
    public function test_user_can_remove_member_from_branch_group()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'branch',
            'parent_group_id' => $coreGroup->id,
            'name' => 'テスト班グループ',
        ]);

        // コアグループにメンバーを追加
        $coreMember1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $coreMember2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $otherUser->id,
            'is_registered' => true,
        ]);

        // 班グループにメンバーを追加
        $branchMember1 = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $coreMember1->name,
            'email' => $coreMember1->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $branchMember2 = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $coreMember2->name,
            'email' => $coreMember2->email,
            'user_id' => $otherUser->id,
            'is_registered' => true,
        ]);

        // 他のユーザーのメンバーを削除
        $response = $this->actingAs($user)
            ->delete(route('branch-groups.members.destroy', [$branchGroup, $branchMember2]));

        $response->assertRedirect(route('branch-groups.show', $branchGroup));
        $this->assertSoftDeleted('members', [
            'id' => $branchMember2->id,
        ]);

        // 班グループ自体は削除されていないことを確認
        $this->assertDatabaseHas('groups', [
            'id' => $branchGroup->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * 自分自身を班グループから削除しようとした場合にエラーになるかテスト
     */
    public function test_user_cannot_remove_self_from_branch_group()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'branch',
            'parent_group_id' => $coreGroup->id,
            'name' => 'テスト班グループ',
        ]);

        // コアグループにメンバーを追加
        $coreMember = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        // 班グループにメンバーを追加
        $branchMember = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $coreMember->name,
            'email' => $coreMember->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        // 自分自身を削除しようとする
        $response = $this->actingAs($user)
            ->delete(route('branch-groups.members.destroy', [$branchGroup, $branchMember]));

        $response->assertRedirect();
        $response->assertSessionHas('error', '自分自身をメンバーから削除することはできません');

        // メンバーが削除されていないことを確認
        $this->assertDatabaseHas('members', [
            'id' => $branchMember->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * 最後のメンバーを削除した場合に班グループも削除されるかテスト
     */
    public function test_branch_group_is_deleted_when_last_member_is_removed()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'branch',
            'parent_group_id' => $coreGroup->id,
            'name' => 'テスト班グループ',
        ]);

        // コアグループにメンバーを追加
        $coreMember1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $coreMember2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $otherUser->id,
            'is_registered' => true,
        ]);

        // 班グループにメンバーを追加（他のユーザーのみ）
        $branchMember = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $coreMember2->name,
            'email' => $coreMember2->email,
            'user_id' => $otherUser->id,
            'is_registered' => true,
        ]);

        // 唯一のメンバーを削除
        $response = $this->actingAs($user)
            ->delete(route('branch-groups.members.destroy', [$branchGroup, $branchMember]));

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $this->assertSoftDeleted('members', [
            'id' => $branchMember->id,
        ]);

        // 班グループも削除されていることを確認
        $this->assertSoftDeleted('groups', [
            'id' => $branchGroup->id,
        ]);
    }

    /**
     * 同じユーザーを班グループに重複して追加できないことをテスト
     */
    public function test_cannot_add_duplicate_user_to_branch_group()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'branch',
            'parent_group_id' => $coreGroup->id,
            'name' => 'テスト班グループ',
        ]);

        // コアグループにメンバーを追加
        $coreMember1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $coreMember2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $otherUser->id,
            'is_registered' => true,
        ]);

        // 班グループにメンバーを追加
        $branchMember = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $coreMember2->name,
            'email' => $coreMember2->email,
            'user_id' => $otherUser->id,
            'is_registered' => true,
        ]);

        // 同じユーザーを再度追加しようとする
        $response = $this->actingAs($user)
            ->post(route('branch-groups.members.store', $branchGroup), [
                'member_id' => $coreMember2->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'このユーザーは既に班グループのメンバーです');

        // 同じユーザーのメンバーが1人だけであることを確認
        $this->assertEquals(1, Member::where('group_id', $branchGroup->id)
            ->where('user_id', $otherUser->id)
            ->count());
    }
}
