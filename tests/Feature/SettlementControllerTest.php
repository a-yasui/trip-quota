<?php

namespace Tests\Feature;

use App\Models\ExpenseSettlement;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TravelPlan $travelPlan;
    private Member $member;
    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
            'owner_user_id' => $this->user->id,
        ]);
        
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);

        $this->group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);
    }

    public function test_index_displays_settlements()
    {
        $settlement = ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'amount' => 1000,
            'currency' => 'JPY',
            'is_settled' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.settlements.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('settlements.index');
        $response->assertViewHas(['travelPlan', 'settlements', 'settlementsByCurrency', 'statistics']);
        $response->assertSee('精算管理');
    }

    public function test_index_shows_empty_state_when_no_settlements()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.settlements.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('精算情報がありません');
    }

    public function test_calculate_settlements_redirects_with_success()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.settlements.calculate', $this->travelPlan->uuid));

        $response->assertRedirect(route('travel-plans.settlements.index', $this->travelPlan->uuid));
        $response->assertSessionHas('info', '精算が必要な費用がありません。');
    }

    public function test_show_displays_settlement_details()
    {
        $payee = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => '受取者',
        ]);

        $settlement = ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $payee->id,
            'amount' => 1500,
            'currency' => 'JPY',
            'is_settled' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.settlements.show', [$this->travelPlan->uuid, $settlement->id]));

        $response->assertStatus(200);
        $response->assertViewIs('settlements.show');
        $response->assertViewHas(['travelPlan', 'settlement']);
        $response->assertSee('精算詳細');
        $response->assertSee('1,500 JPY');
        $response->assertSee('受取者');
    }

    public function test_mark_as_completed_updates_settlement()
    {
        $settlement = ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'is_settled' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.settlements.complete', [$this->travelPlan->uuid, $settlement->id]));

        $response->assertRedirect(route('travel-plans.settlements.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success', '精算を完了として記録しました。');

        $settlement->refresh();
        $this->assertTrue($settlement->is_settled);
        $this->assertNotNull($settlement->settled_at);
    }

    public function test_reset_clears_pending_settlements()
    {
        ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'is_settled' => false,
        ]);

        ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'is_settled' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.settlements.reset', $this->travelPlan->uuid));

        $response->assertRedirect(route('travel-plans.settlements.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success', '精算情報をリセットしました。');

        // 未精算のもののみ削除される
        $this->assertEquals(1, ExpenseSettlement::where('travel_plan_id', $this->travelPlan->id)->count());
        $this->assertEquals(1, ExpenseSettlement::where('travel_plan_id', $this->travelPlan->id)
            ->where('is_settled', true)->count());
    }

    public function test_unauthorized_user_cannot_access_settlements()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.settlements.index', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_calculate_settlements()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->post(route('travel-plans.settlements.calculate', $this->travelPlan->uuid));

        $response->assertRedirect(route('travel-plans.settlements.index', $this->travelPlan->uuid));
        $response->assertSessionHas('error');
    }

    public function test_settlement_does_not_belong_to_travel_plan_returns_404()
    {
        $otherTravelPlan = TravelPlan::factory()->create();
        $settlement = ExpenseSettlement::factory()->create([
            'travel_plan_id' => $otherTravelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.settlements.show', [$this->travelPlan->uuid, $settlement->id]));

        $response->assertStatus(404);
    }

    public function test_mark_completed_already_settled_settlement_shows_error()
    {
        $settlement = ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'is_settled' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.settlements.complete', [$this->travelPlan->uuid, $settlement->id]));

        $response->assertRedirect(route('travel-plans.settlements.index', $this->travelPlan->uuid));
        $response->assertSessionHas('error');
    }

    public function test_index_displays_statistics_correctly()
    {
        ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'amount' => 1000,
            'currency' => 'JPY',
            'is_settled' => false,
        ]);

        ExpenseSettlement::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'payee_member_id' => $this->member->id,
            'amount' => 2000,
            'currency' => 'JPY',
            'is_settled' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.settlements.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('2件'); // 総精算件数
        $response->assertSee('1件'); // 完了済み、未精算
        $response->assertSee('3,000 JPY'); // 総額
    }
}