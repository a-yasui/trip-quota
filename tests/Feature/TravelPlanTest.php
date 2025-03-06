<?php

namespace Tests\Feature;

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
        $response->assertSee('韓国ソウル旅行');
        $response->assertSee('沖縄社員旅行');
    }
}
