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
                         ->get('/travel-plans/' . $travelPlan->id);

        $response->assertStatus(200);
        $response->assertViewIs('travel-plans.show');
        $response->assertSee($travelPlan->title);
        $response->assertSee($user->name);
    }
}
