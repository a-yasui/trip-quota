<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the travel plans index page can be rendered.
     */
    public function test_travel_plans_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/travel-plans');

        $response->assertStatus(200);
        $response->assertViewIs('travel-plans.index');
    }

    /**
     * Test that the travel plans index page contains expected elements.
     */
    public function test_travel_plans_index_contains_expected_elements(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/travel-plans');

        $response->assertSee('旅行計画一覧');
        $response->assertSee('旅行名');
        $response->assertSee('期間');
        $response->assertSee('メンバー数');
        $response->assertSee('ステータス');
    }

    /**
     * Test that the travel plan creation page can be rendered.
     */
    public function test_travel_plan_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/travel-plans/create');

        $response->assertStatus(200);
        $response->assertViewIs('travel-plans.create');
        $response->assertSee('旅行計画作成');
        $response->assertSee('旅行名');
        $response->assertSee('出発日');
        $response->assertSee('帰宅日');
        $response->assertSee('タイムゾーン');
    }

    /**
     * Test that a travel plan can be created.
     */
    public function test_travel_plan_can_be_created(): void
    {
        $user = User::factory()->create();

        $travelPlanData = [
            'title' => '韓国ソウル旅行',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(33)->format('Y-m-d'),
            'timezone' => 'Asia/Seoul',
        ];

        $response = $this->actingAs($user)
            ->post('/travel-plans', $travelPlanData);

        $this->assertDatabaseHas('travel_plans', [
            'title' => '韓国ソウル旅行',
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);

        $travelPlan = TravelPlan::where('title', '韓国ソウル旅行')->first();

        $this->assertDatabaseHas('groups', [
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
        ]);

        $coreGroup = Group::where('travel_plan_id', $travelPlan->id)
            ->where('type', 'core')
            ->first();

        $this->assertDatabaseHas('members', [
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $response->assertSessionHas('success');
    }

    /**
     * Test that a travel plan cannot be created with invalid data.
     */
    public function test_travel_plan_cannot_be_created_with_invalid_data(): void
    {
        $user = User::factory()->create();

        // 出発日が過去の日付
        $travelPlanData = [
            'title' => '韓国ソウル旅行',
            'departure_date' => now()->subDays(1)->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'timezone' => 'Asia/Seoul',
        ];

        $response = $this->actingAs($user)
            ->post('/travel-plans', $travelPlanData);

        $response->assertSessionHasErrors('departure_date');

        // 帰宅日が出発日より前
        $travelPlanData = [
            'title' => '韓国ソウル旅行',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(29)->format('Y-m-d'),
            'timezone' => 'Asia/Seoul',
        ];

        $response = $this->actingAs($user)
            ->post('/travel-plans', $travelPlanData);

        $response->assertSessionHasErrors('return_date');

        // タイトルが空
        $travelPlanData = [
            'title' => '',
            'departure_date' => now()->addDays(30)->format('Y-m-d'),
            'return_date' => now()->addDays(33)->format('Y-m-d'),
            'timezone' => 'Asia/Seoul',
        ];

        $response = $this->actingAs($user)
            ->post('/travel-plans', $travelPlanData);

        $response->assertSessionHasErrors('title');
    }

    /**
     * Test that a travel plan detail page can be rendered.
     */
    public function test_travel_plan_detail_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);

        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
            'name' => $travelPlan->title,
        ]);

        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlan->id);

        $response->assertStatus(200);
        $response->assertViewIs('travel-plans.show');
        $response->assertSee($travelPlan->title);
        $response->assertSee($user->name);
    }

    /**
     * Test that a travel plan edit page can be rendered.
     */
    public function test_travel_plan_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'departure_date' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlan->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('travel-plans.edit');
        $response->assertSee($travelPlan->title);
        $response->assertSee('旅行計画編集');
    }

    /**
     * Test that a travel plan can be updated before departure date.
     */
    public function test_travel_plan_can_be_updated_before_departure(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'title' => '韓国ソウル旅行',
            'departure_date' => now()->addDays(10),
            'return_date' => now()->addDays(15),
            'timezone' => 'Asia/Tokyo',
        ]);

        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
            'name' => $travelPlan->title,
        ]);

        $updatedData = [
            'title' => '韓国ソウル旅行（更新）',
            'departure_date' => now()->addDays(12)->format('Y-m-d'),
            'return_date' => now()->addDays(17)->format('Y-m-d'),
            'timezone' => 'Asia/Seoul',
        ];

        $response = $this->actingAs($user)
            ->put('/travel-plans/'.$travelPlan->id, $updatedData);

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('travel_plans', [
            'id' => $travelPlan->id,
            'title' => '韓国ソウル旅行（更新）',
            'timezone' => 'Asia/Seoul',
        ]);

        // コアグループの名前も更新されていることを確認
        $this->assertDatabaseHas('groups', [
            'id' => $coreGroup->id,
            'name' => '韓国ソウル旅行（更新）',
        ]);
    }

    /**
     * Test that a travel plan cannot be updated after departure date except for return date.
     */
    public function test_travel_plan_cannot_be_updated_after_departure_except_return_date(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'title' => '韓国ソウル旅行',
            'departure_date' => now()->subDays(2), // 2日前に出発済み
            'return_date' => null, // 帰宅日は未設定
            'timezone' => 'Asia/Tokyo',
        ]);

        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'core',
            'name' => $travelPlan->title,
        ]);

        $updatedData = [
            'title' => '韓国ソウル旅行（更新）',
            'departure_date' => now()->subDays(1)->format('Y-m-d'), // 変更しようとしても
            'return_date' => now()->addDays(3)->format('Y-m-d'), // 帰宅日のみ設定可能
            'timezone' => 'Asia/Seoul', // 変更しようとしても
        ];

        $response = $this->actingAs($user)
            ->put('/travel-plans/'.$travelPlan->id, $updatedData);

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $response->assertSessionHas('success');

        // タイトル、出発日、タイムゾーンは変更されていないことを確認
        $this->assertDatabaseHas('travel_plans', [
            'id' => $travelPlan->id,
            'title' => '韓国ソウル旅行', // 変更されていない
            'timezone' => 'Asia/Tokyo', // 変更されていない
        ]);

        // 帰宅日のみ更新されていることを確認
        $updatedTravelPlan = TravelPlan::find($travelPlan->id);
        $this->assertEquals(now()->addDays(3)->format('Y-m-d'), $updatedTravelPlan->return_date->format('Y-m-d'));

        // コアグループの名前も変更されていないことを確認
        $this->assertDatabaseHas('groups', [
            'id' => $coreGroup->id,
            'name' => '韓国ソウル旅行', // 変更されていない
        ]);
    }

    /**
     * Test that a travel plan's return date cannot be updated after departure if already set.
     */
    public function test_travel_plan_return_date_cannot_be_updated_after_departure_if_already_set(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'title' => '韓国ソウル旅行',
            'departure_date' => now()->subDays(2), // 2日前に出発済み
            'return_date' => now()->addDays(3), // 帰宅日は既に設定済み
            'timezone' => 'Asia/Tokyo',
        ]);

        $updatedData = [
            'title' => '韓国ソウル旅行（更新）',
            'departure_date' => now()->subDays(1)->format('Y-m-d'),
            'return_date' => now()->addDays(5)->format('Y-m-d'), // 変更しようとしても
            'timezone' => 'Asia/Seoul',
        ];

        $response = $this->actingAs($user)
            ->put('/travel-plans/'.$travelPlan->id, $updatedData);

        $response->assertRedirect(route('travel-plans.show', $travelPlan));
        $response->assertSessionHas('success');

        // 帰宅日も含めて何も変更されていないことを確認
        $this->assertDatabaseHas('travel_plans', [
            'id' => $travelPlan->id,
            'title' => '韓国ソウル旅行',
            'timezone' => 'Asia/Tokyo',
        ]);

        $updatedTravelPlan = TravelPlan::find($travelPlan->id);
        $this->assertEquals(now()->addDays(3)->format('Y-m-d'), $updatedTravelPlan->return_date->format('Y-m-d'));
    }

    /**
     * Test that branch groups in travel plan detail page have links to branch group detail page.
     */
    public function test_travel_plan_detail_shows_branch_group_links(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);

        // コアグループを作成
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => \App\Enums\GroupType::CORE,
            'name' => 'コアグループ',
        ]);

        // 班グループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => \App\Enums\GroupType::BRANCH,
            'name' => '班グループA',
        ]);

        // ユーザーをメンバーとして追加
        Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlan->id);

        $response->assertStatus(200);

        // 班グループの名前が表示されていることを確認
        $response->assertSee('班グループA');

        // 班グループへのリンクが存在することを確認
        $branchGroupUrl = route('branch-groups.show', $branchGroup);
        $response->assertSee($branchGroupUrl);
    }

    /**
     * Test that core groups in travel plan detail page do not have links.
     */
    public function test_travel_plan_detail_does_not_show_core_group_links(): void
    {
        $user = User::factory()->create();

        $travelPlan = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
        ]);

        // コアグループを作成
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => \App\Enums\GroupType::CORE,
            'name' => 'コアグループ',
        ]);

        // ユーザーをメンバーとして追加
        Member::factory()->create([
            'group_id' => $coreGroup->id,
            'user_id' => $user->id,
            'is_registered' => true,
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlan->id);

        $response->assertStatus(200);

        // コアグループの名前が表示されていることを確認
        $response->assertSee('コアグループ');

        // コアグループへのリンクが存在しないことを確認
        $coreGroupUrl = route('branch-groups.show', $coreGroup);
        $response->assertDontSee($coreGroupUrl);
    }

    /**
     * Test that edit button is shown only before departure or when return date is not set.
     */
    public function test_edit_button_is_shown_only_before_departure_or_when_return_date_not_set(): void
    {
        $user = User::factory()->create();

        // ケース1: 出発前の旅行計画
        $travelPlanBefore = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'departure_date' => now()->addDays(10),
            'return_date' => now()->addDays(15),
        ]);

        Group::factory()->create([
            'travel_plan_id' => $travelPlanBefore->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlanBefore->id);

        $response->assertStatus(200);
        $response->assertSee('編集');

        // ケース2: 出発後だが帰宅日が未設定の旅行計画
        $travelPlanAfterNoReturn = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'departure_date' => now()->subDays(2),
            'return_date' => null,
        ]);

        Group::factory()->create([
            'travel_plan_id' => $travelPlanAfterNoReturn->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlanAfterNoReturn->id);

        $response->assertStatus(200);
        $response->assertSee('編集');

        // ケース3: 出発後で帰宅日が設定済みの旅行計画
        $travelPlanAfterWithReturn = TravelPlan::factory()->create([
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'departure_date' => now()->subDays(2),
            'return_date' => now()->addDays(3),
        ]);

        Group::factory()->create([
            'travel_plan_id' => $travelPlanAfterWithReturn->id,
            'type' => 'core',
        ]);

        $response = $this->actingAs($user)
            ->get('/travel-plans/'.$travelPlanAfterWithReturn->id);

        $response->assertStatus(200);
        $response->assertDontSee('編集'); // 編集ボタンが表示されないことを確認
    }
}
