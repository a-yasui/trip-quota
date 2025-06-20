<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TravelPlan $travelPlan;

    private Group $coreGroup;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
            'creator_user_id' => $this->user->id,
            'owner_user_id' => $this->user->id,
        ]);

        $this->coreGroup = Group::factory()->create([
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

    public function test_index_displays_groups_for_authenticated_member()
    {
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '班グループ1',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.groups.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('グループ管理');
        $response->assertSee('コアグループ');
        $response->assertSee('班グループ1');
    }

    public function test_create_displays_branch_group_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.groups.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('班グループを作成');
        $response->assertSee('グループ名');
        $response->assertSee('説明');
    }

    public function test_store_creates_branch_group_with_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.groups.store', $this->travelPlan->uuid), [
                'name' => '新しい班グループ',
                'description' => 'テスト用の班グループです',
            ]);

        $response->assertRedirect(route('travel-plans.groups.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('groups', [
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '新しい班グループ',
            'description' => 'テスト用の班グループです',
        ]);

        $group = Group::where('name', '新しい班グループ')->first();
        $this->assertNotNull($group->branch_key);
        $this->assertStringStartsWith('branch_', $group->branch_key);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.groups.store', $this->travelPlan->uuid), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_show_displays_group_details()
    {
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '班グループ詳細',
            'description' => 'テスト用グループ',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.groups.show', [$this->travelPlan->uuid, $branchGroup->id]));

        $response->assertStatus(200);
        $response->assertSee('班グループ詳細');
        $response->assertSee('テスト用グループ');
        $response->assertSee('班グループ');
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '編集テストグループ',
            'description' => '編集前の説明',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.groups.edit', [$this->travelPlan->uuid, $branchGroup->id]));

        $response->assertStatus(200);
        $response->assertSee('グループ編集');
        $response->assertSee('編集テストグループ');
        $response->assertSee('編集前の説明');
    }

    public function test_edit_cannot_edit_core_group()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.groups.edit', [$this->travelPlan->uuid, $this->coreGroup->id]));

        $response->assertStatus(403);
    }

    public function test_update_modifies_group_with_valid_data()
    {
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '更新前グループ',
            'description' => '更新前の説明',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.groups.update', [$this->travelPlan->uuid, $branchGroup->id]), [
                'name' => '更新後グループ',
                'description' => '更新後の説明',
            ]);

        $response->assertRedirect(route('travel-plans.groups.show', [$this->travelPlan->uuid, $branchGroup->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('groups', [
            'id' => $branchGroup->id,
            'name' => '更新後グループ',
            'description' => '更新後の説明',
        ]);
    }

    public function test_destroy_deletes_empty_branch_group()
    {
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '削除テストグループ',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.groups.destroy', [$this->travelPlan->uuid, $branchGroup->id]));

        $response->assertRedirect(route('travel-plans.groups.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('groups', [
            'id' => $branchGroup->id,
        ]);
    }

    public function test_destroy_cannot_delete_core_group()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.groups.destroy', [$this->travelPlan->uuid, $this->coreGroup->id]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('groups', [
            'id' => $this->coreGroup->id,
        ]);
    }

    public function test_unauthorized_user_cannot_access_groups()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.groups.index', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_create_group()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->post(route('travel-plans.groups.store', $this->travelPlan->uuid), [
                'name' => '不正なグループ',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
    }

    public function test_join_group_successfully()
    {
        // 新しいユーザーとメンバーを作成
        $newUser = User::factory()->create();
        $newMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $newUser->id,
            'is_confirmed' => true,
        ]);
        
        // コアグループに参加させる（テストのため）
        $this->coreGroup->members()->attach($newMember->id);
        
        // 新しい班グループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'テスト班グループ',
        ]);

        // 新しいユーザーが班グループに参加する
        $response = $this->actingAs($newUser)
            ->post(route('travel-plans.groups.join', [$this->travelPlan->uuid, $branchGroup->id]));

        $response->assertRedirect(route('travel-plans.groups.show', [$this->travelPlan->uuid, $branchGroup->id]));
        $response->assertSessionHas('success', 'グループに参加しました。');

        // データベースで参加を確認
        $this->assertDatabaseHas('group_member', [
            'group_id' => $branchGroup->id,
            'member_id' => $newMember->id,
        ]);
    }

    public function test_join_group_fails_when_already_member()
    {
        // 新しいユーザーとメンバーを作成
        $newUser = User::factory()->create();
        $newMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $newUser->id,
            'is_confirmed' => true,
        ]);
        
        // コアグループに参加させる
        $this->coreGroup->members()->attach($newMember->id);
        
        // 新しい班グループを作成し、既に参加させておく
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'テスト班グループ',
        ]);
        $branchGroup->members()->attach($newMember->id);

        // 既に参加しているグループに再度参加しようとする
        $response = $this->actingAs($newUser)
            ->post(route('travel-plans.groups.join', [$this->travelPlan->uuid, $branchGroup->id]));

        $response->assertRedirect(route('travel-plans.groups.show', [$this->travelPlan->uuid, $branchGroup->id]));
        $response->assertSessionHas('info', '既にこのグループに参加しています。');
    }

    public function test_join_group_fails_when_not_travel_plan_member()
    {
        // 旅行プランのメンバーではないユーザー
        $otherUser = User::factory()->create();
        
        // 新しい班グループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'テスト班グループ',
        ]);

        // 非メンバーがグループに参加しようとする
        $response = $this->actingAs($otherUser)
            ->post(route('travel-plans.groups.join', [$this->travelPlan->uuid, $branchGroup->id]));

        // TravelPlanServiceで例外が発生し、catchブロックでback()が実行される
        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
    }
}
