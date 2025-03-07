<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupMemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メンバー追加フォームが表示されるかテスト
     */
    public function test_user_can_view_member_create_form()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->get(route('groups.members.create', $group));

        $response->assertStatus(200);
        $response->assertViewIs('group-members.create');
    }

    /**
     * 名前のみでメンバーを追加できるかテスト
     */
    public function test_user_can_add_member_with_name_only()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => 'テストメンバー',
            ]);

        $response->assertRedirect(route('travel-plans.show', $group->travelPlan));
        $this->assertDatabaseHas('members', [
            'name' => 'テストメンバー',
            'group_id' => $group->id,
            'is_registered' => 0,
        ]);
    }

    /**
     * メールアドレスのみでメンバーを追加できるかテスト
     */
    public function test_user_can_add_member_with_email_only()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'email' => 'test@example.com',
            ]);

        $response->assertRedirect(route('travel-plans.show', $group->travelPlan));
        $this->assertDatabaseHas('members', [
            'email' => 'test@example.com',
            'group_id' => $group->id,
            'is_registered' => 0,
        ]);
    }

    /**
     * 登録ユーザーのメールアドレスを使用した場合に自動連携されるかテスト
     */
    public function test_user_can_add_registered_member()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => 'テスト登録ユーザー',
                'email' => $otherUser->email,
            ]);

        $response->assertRedirect(route('travel-plans.show', $group->travelPlan));
        $this->assertDatabaseHas('members', [
            'name' => 'テスト登録ユーザー',
            'email' => $otherUser->email,
            'user_id' => $otherUser->id,
            'group_id' => $group->id,
            'is_registered' => 1,
        ]);
    }

    /**
     * メンバーを削除できるかテスト
     */
    public function test_user_can_delete_member()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $member = Member::factory()->create([
            'group_id' => $group->id,
            'name' => '削除対象メンバー',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('groups.members.destroy', [$group, $member]));

        $response->assertRedirect();
        $this->assertSoftDeleted('members', [
            'id' => $member->id,
        ]);
    }

    /**
     * 自分自身を削除しようとした場合にエラーになるかテスト
     */
    public function test_user_cannot_delete_self()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        $member = Member::factory()->create([
            'group_id' => $group->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('groups.members.destroy', [$group, $member]));

        $response->assertRedirect();
        $response->assertSessionHas('error', '自分自身をメンバーから削除することはできません。');
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * 同じ名前のメンバーを追加しようとした場合にエラーになるかテスト
     */
    public function test_cannot_add_member_with_duplicate_name()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        
        // 最初のメンバーを追加
        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => '重複名前テスト',
            ]);
        
        $response->assertRedirect();
        
        // 同じ名前で2人目のメンバーを追加しようとする
        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => '重複名前テスト',
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error', '同じ名前のメンバーが既に登録されています');
        
        // データベースに1人しか登録されていないことを確認
        $this->assertEquals(1, Member::where('name', '重複名前テスト')->count());
    }
    
    /**
     * 同じメールアドレスのメンバーを追加しようとした場合にエラーになるかテスト
     */
    public function test_cannot_add_member_with_duplicate_email()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        
        // 最初のメンバーを追加
        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => 'メンバー1',
                'email' => 'duplicate@example.com',
            ]);
        
        $response->assertRedirect();
        
        // 同じメールアドレスで2人目のメンバーを追加しようとする
        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => 'メンバー2',
                'email' => 'duplicate@example.com',
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'このメールアドレスは既に登録されています');
        
        // データベースに1人しか登録されていないことを確認
        $this->assertEquals(1, Member::where('email', 'duplicate@example.com')->count());
    }
    
    /**
     * 同じユーザーを追加しようとした場合にエラーになるかテスト
     */
    public function test_cannot_add_same_user_twice()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);
        
        // 最初のメンバーを追加
        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => 'テストユーザー',
                'email' => $otherUser->email,
            ]);
        
        $response->assertRedirect();
        
        // 同じユーザーで2人目のメンバーを追加しようとする
        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => '別名テストユーザー',
                'email' => $otherUser->email,
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'このメールアドレスは既に登録されています');
        
        // データベースに1人しか登録されていないことを確認
        $this->assertEquals(1, Member::where('user_id', $otherUser->id)->count());
    }
    
    /**
     * 名前とメールアドレスの両方が未入力の場合にバリデーションエラーになるかテスト
     */
    public function test_validation_fails_when_both_name_and_email_are_empty()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);
        $group = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->post(route('groups.members.store', $group), [
                'name' => '',
                'email' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email']);
    }
}
