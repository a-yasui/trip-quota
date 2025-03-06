<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the dashboard page can be rendered.
     */
    public function test_dashboard_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    /**
     * Test that the dashboard page contains expected elements.
     */
    public function test_dashboard_contains_expected_elements(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('ダッシュボード');
        $response->assertSee('現在の旅行計画');
        $response->assertSee('最近の経費');
        $response->assertSee('最近の通知');
        $response->assertSee('今後の予定');
    }
}
