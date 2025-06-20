<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
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
            'departure_date' => '2024-07-01',
            'return_date' => '2024-07-05',
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

    public function test_index_displays_expenses()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => 'テスト費用',
            'amount' => 5000,
            'currency' => 'JPY',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('費用管理');
        $response->assertSee('テスト費用');
        $response->assertSee('5,000 JPY');
    }

    public function test_index_shows_empty_state_when_no_expenses()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('費用がありません');
        $response->assertSee('最初の費用を追加してみましょう');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('費用を追加');
        $response->assertSee('費用タイトル');
        $response->assertSee('金額');
        $response->assertSee('対象グループ');
        $response->assertSee('支払い者');
    }

    public function test_store_creates_expense_successfully()
    {
        $expenseData = [
            'title' => '新しい費用',
            'description' => 'テスト説明',
            'amount' => 3000,
            'currency' => 'JPY',
            'expense_date' => '2024-07-02',
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'member_assignments' => [
                [
                    'member_id' => $this->member->id,
                    'is_participating' => true,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan->uuid), $expenseData);

        $response->assertRedirect(route('travel-plans.expenses.create', $this->travelPlan->uuid));
        $response->assertSessionHas('success', '費用を追加しました。次の費用を追加できます。');

        $this->assertDatabaseHas('expenses', [
            'travel_plan_id' => $this->travelPlan->id,
            'title' => '新しい費用',
            'amount' => 3000,
            'currency' => 'JPY',
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan->uuid), []);

        $response->assertSessionHasErrors(['title', 'amount', 'currency', 'expense_date', 'group_id', 'paid_by_member_id']);
    }

    public function test_store_validates_positive_amount()
    {
        $expenseData = [
            'title' => 'テスト費用',
            'amount' => -100, // 負の値
            'currency' => 'JPY',
            'expense_date' => '2024-07-02',
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan->uuid), $expenseData);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_show_displays_expense_details()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => '費用詳細テスト',
            'description' => 'テスト説明',
            'amount' => 8000,
            'currency' => 'JPY',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.show', [$this->travelPlan->uuid, $expense->id]));

        $response->assertStatus(200);
        $response->assertSee('費用詳細テスト');
        $response->assertSee('テスト説明');
        $response->assertSee('8,000 JPY');
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => '編集用費用',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.edit', [$this->travelPlan->uuid, $expense->id]));

        $response->assertStatus(200);
        $response->assertSee('費用を編集');
        $response->assertSee('編集用費用');
    }

    public function test_update_modifies_expense_successfully()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => '元のタイトル',
        ]);

        $updateData = [
            'title' => '更新されたタイトル',
            'description' => '更新された説明',
            'amount' => 4000,
            'currency' => 'USD',
            'expense_date' => '2024-07-03',
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.expenses.update', [$this->travelPlan->uuid, $expense->id]), $updateData);

        $response->assertRedirect(route('travel-plans.expenses.show', [$this->travelPlan->uuid, $expense->id]));
        $response->assertSessionHas('success', '費用を更新しました。');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'title' => '更新されたタイトル',
            'amount' => 4000,
            'currency' => 'USD',
        ]);
    }

    public function test_destroy_deletes_expense()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.expenses.destroy', [$this->travelPlan->uuid, $expense->id]));

        $response->assertRedirect(route('travel-plans.expenses.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success', '費用を削除しました。');

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_unauthorized_user_cannot_access_expenses()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_expense_currency_aggregation()
    {
        // 複数通貨の費用を作成
        Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 1000,
            'currency' => 'JPY',
        ]);

        Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 50,
            'currency' => 'USD',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('複数通貨');
        $response->assertSee('通貨別集計');
        $response->assertSee('1,000');
        $response->assertSee('JPY');
        $response->assertSee('50');
        $response->assertSee('USD');
    }
}
