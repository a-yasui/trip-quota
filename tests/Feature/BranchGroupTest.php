<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 班グループ作成フォームが表示されるかテスト
     */
    public function test_user_can_view_branch_group_create_form()
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
        
        // コアグループにメンバーを追加
        Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('travel-plans.branch-groups.create', $travelPlan));

        $response->assertStatus(200);
        $response->assertViewIs('branch-groups.create');
    }

    /**
     * 班グループを作成できるかテスト
     */
    public function test_user_can_create_branch_group()
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
        
        // コアグループにメンバーを追加
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('travel-plans.branch-groups.store', $travelPlan), [
                'name' => 'テスト班グループ',
                'members' => [$member->id],
            ]);

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $this->assertDatabaseHas('groups', [
            'name' => 'テスト班グループ',
            'type' => 'branch',
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        
        // 班グループにメンバーが追加されているか確認
        $branchGroup = Group::where('name', 'テスト班グループ')->first();
        $this->assertDatabaseHas('members', [
            'name' => $member->name,
            'group_id' => $branchGroup->id,
        ]);
    }

    /**
     * 班グループの詳細を表示できるかテスト
     */
    public function test_user_can_view_branch_group_details()
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

        $response = $this->actingAs($user)
            ->get(route('branch-groups.show', $branchGroup));

        $response->assertStatus(200);
        $response->assertViewIs('branch-groups.show');
        $response->assertSee('テスト班グループ');
        $response->assertSee($branchMember->name);
    }

    /**
     * 班グループを編集できるかテスト
     */
    public function test_user_can_edit_branch_group()
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
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);
        
        // 班グループにメンバーを追加
        Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $member->name,
            'email' => $member->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('branch-groups.edit', $branchGroup));

        $response->assertStatus(200);
        $response->assertViewIs('branch-groups.edit');
        
        $response = $this->actingAs($user)
            ->put(route('branch-groups.update', $branchGroup), [
                'name' => '更新された班グループ',
                'description' => 'テスト説明文',
            ]);

        $response->assertRedirect(route('branch-groups.show', $branchGroup));
        $this->assertDatabaseHas('groups', [
            'id' => $branchGroup->id,
            'name' => '更新された班グループ',
            'description' => 'テスト説明文',
        ]);
    }

    /**
     * 班グループを削除できるかテスト
     */
    public function test_user_can_delete_branch_group()
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
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);
        
        // 班グループにメンバーを追加
        $branchMember = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $member->name,
            'email' => $member->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('branch-groups.destroy', $branchGroup));

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $this->assertSoftDeleted('groups', [
            'id' => $branchGroup->id,
        ]);
        $this->assertSoftDeleted('members', [
            'id' => $branchMember->id,
        ]);
    }

    /**
     * 同じ名前の班グループを作成しようとした場合にエラーになるかテスト
     */
    public function test_cannot_create_branch_group_with_duplicate_name()
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
            'name' => '重複名前テスト',
        ]);
        
        // コアグループにメンバーを追加
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('travel-plans.branch-groups.store', $travelPlan), [
                'name' => '重複名前テスト',
                'members' => [$member->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name' => 'この班グループ名は既に使用されています']);
        
        // 同じ名前の班グループが1つしか存在しないことを確認
        $this->assertEquals(1, Group::where('name', '重複名前テスト')->count());
    }

    /**
     * メンバーを選択せずに班グループを作成しようとした場合にエラーになるかテスト
     */
    public function test_cannot_create_branch_group_without_members()
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
        
        // コアグループにメンバーを追加
        Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('travel-plans.branch-groups.store', $travelPlan), [
                'name' => 'テスト班グループ',
                'members' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['members' => 'メンバーを選択してください']);
        
        // 班グループが作成されていないことを確認
        $this->assertDatabaseMissing('groups', [
            'name' => 'テスト班グループ',
            'type' => 'branch',
            'travel_plan_id' => $travelPlan->id,
        ]);
    }

    /**
     * 班グループ複製フォームが表示されるかテスト
     */
    public function test_user_can_view_branch_group_duplicate_form()
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
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);
        
        // 班グループにメンバーを追加
        Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $member->name,
            'email' => $member->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('branch-groups.duplicate', $branchGroup));

        $response->assertStatus(200);
        $response->assertViewIs('branch-groups.duplicate');
        $response->assertSee('テスト班グループ');
    }

    /**
     * 班グループを複製できるかテスト
     */
    public function test_user_can_duplicate_branch_group()
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
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);
        
        // 班グループにメンバーを追加
        $branchMember = Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $member->name,
            'email' => $member->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('branch-groups.store-duplicate', $branchGroup), [
                'name' => '複製された班グループ',
                'description' => 'これは複製された班グループです',
            ]);

        $response->assertRedirect();
        
        // 新しい班グループが作成されたことを確認
        $this->assertDatabaseHas('groups', [
            'name' => '複製された班グループ',
            'description' => 'これは複製された班グループです',
            'type' => 'branch',
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        
        // 複製された班グループにメンバーが追加されているか確認
        $duplicatedGroup = Group::where('name', '複製された班グループ')->first();
        $this->assertDatabaseHas('members', [
            'name' => $branchMember->name,
            'email' => $branchMember->email,
            'user_id' => $user->id,
            'group_id' => $duplicatedGroup->id,
        ]);
    }

    /**
     * 同じ名前の班グループを複製しようとした場合にエラーになるかテスト
     */
    public function test_cannot_duplicate_branch_group_with_duplicate_name()
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
        $existingGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'branch',
            'parent_group_id' => $coreGroup->id,
            'name' => '既存の班グループ',
        ]);
        
        // コアグループにメンバーを追加
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);
        
        // 班グループにメンバーを追加
        Member::factory()->create([
            'group_id' => $branchGroup->id,
            'name' => $member->name,
            'email' => $member->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('branch-groups.store-duplicate', $branchGroup), [
                'name' => '既存の班グループ',
                'description' => 'これは複製された班グループです',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name' => 'この班グループ名は既に使用されています']);
        
        // 同じ名前の班グループが1つしか存在しないことを確認
        $this->assertEquals(1, Group::where('name', '既存の班グループ')->count());
    }
}
