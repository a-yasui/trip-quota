<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\Invitation\InvitationService;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TravelPlan $travelPlan1;
    private TravelPlan $travelPlan2;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $this->user->id]);

        // Create travel plans and memberships
        $this->travelPlan1 = TravelPlan::factory()->create([
            'plan_name' => '春の旅行',
            'departure_date' => now()->addDays(30),
            'return_date' => now()->addDays(35),
        ]);

        $this->travelPlan2 = TravelPlan::factory()->create([
            'plan_name' => '夏の旅行',
            'departure_date' => now()->addDays(100),
            'return_date' => now()->addDays(107),
        ]);

        // Create confirmed memberships
        Member::factory()->create([
            'travel_plan_id' => $this->travelPlan1->id,
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'is_confirmed' => true,
        ]);

        Member::factory()->create([
            'travel_plan_id' => $this->travelPlan2->id,
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'is_confirmed' => true,
        ]);

        // Create core groups for invitation testing
        Group::factory()->create([
            'travel_plan_id' => $this->travelPlan1->id,
            'type' => 'CORE',
            'name' => 'コアグループ',
        ]);
    }

    public function test_dashboard_displays_user_travel_plans()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('travelPlans');
        $response->assertViewHas('pendingInvitationsCount');

        // Check that travel plans are displayed
        $response->assertSee('春の旅行');
        $response->assertSee('夏の旅行');
        $response->assertSee('参加している旅行プラン');
    }

    public function test_dashboard_shows_empty_state_when_no_travel_plans()
    {
        // Create a user with no travel plans
        $userWithoutPlans = User::factory()->create();

        $response = $this->actingAs($userWithoutPlans)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('旅行プランがありません');
        $response->assertSee('新しい旅行プランを作成するか、招待を受けてメンバーになりましょう。');
    }

    public function test_dashboard_shows_pending_invitations_count()
    {
        // Create an invitation for the user
        $invitationService = app(InvitationService::class);
        $inviterUser = User::factory()->create();
        $inviterAccount = Account::factory()->create(['user_id' => $inviterUser->id]);
        
        Member::factory()->create([
            'travel_plan_id' => $this->travelPlan1->id,
            'user_id' => $inviterUser->id,
            'account_id' => $inviterAccount->id,
            'is_confirmed' => true,
        ]);

        $invitationService->createInvitation(
            $this->travelPlan1,
            $inviterUser,
            $this->user->email,
            'Test User'
        );

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('1'); // Invitation count badge
    }

    public function test_dashboard_displays_travel_plan_details()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check travel plan details are shown
        $response->assertSee($this->travelPlan1->departure_date->format('Y/m/d'));
        $response->assertSee($this->travelPlan1->return_date->format('Y/m/d'));
        $response->assertSee($this->travelPlan2->departure_date->format('Y/m/d'));
        $response->assertSee($this->travelPlan2->return_date->format('Y/m/d'));
    }

    public function test_dashboard_shows_quick_actions()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('クイックアクション');
        $response->assertSee('旅行プラン一覧');
        $response->assertSee('新しい旅行プラン');
        $response->assertSee('招待一覧');
    }

    public function test_dashboard_shows_account_information()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('アカウント情報');
        
        // Since the user has an account, we should see account info displayed
        $this->assertTrue($this->user->accounts->count() > 0);
    }

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get(route('dashboard'));
        
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_limits_displayed_travel_plans_to_three()
    {
        // Create additional travel plans
        for ($i = 3; $i <= 5; $i++) {
            $travelPlan = TravelPlan::factory()->create([
                'plan_name' => "旅行プラン {$i}",
                'departure_date' => now()->addDays(30 + $i),
            ]);

            Member::factory()->create([
                'travel_plan_id' => $travelPlan->id,
                'user_id' => $this->user->id,
                'account_id' => $this->user->accounts->first()->id,
                'is_confirmed' => true,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('他 2 件の旅行プランを見る');
    }
}