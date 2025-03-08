<?php

namespace Tests\Feature;

use App\Enums\GroupType;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $travelPlan;
    protected $coreGroup;
    protected $member;

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザーを作成
        $this->user = User::factory()->create();

        // 旅行計画を作成
        $this->travelPlan = TravelPlan::factory()->create([
            'creator_id' => $this->user->id,
            'deletion_permission_holder_id' => $this->user->id,
        ]);

        // コアグループを作成
        $this->coreGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // メンバーを作成
        $this->member = Member::factory()->create([
            'user_id' => $this->user->id,
            'group_id' => $this->coreGroup->id,
            'is_registered' => true,
        ]);
    }

    /**
     * 経費一覧ページのテスト
     */
    public function test_expense_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.index');
        $response->assertViewHas('expenses');
    }

    /**
     * 経費作成ページのテスト
     */
    public function test_expense_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.create', $this->travelPlan));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.create');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('branchGroups');
        $response->assertViewHas('members');
    }

    /**
     * 経費の作成テスト
     */
    public function test_expense_can_be_created(): void
    {
        $data = [
            'description' => '食事代',
            'amount' => 3000,
            'currency' => 'JPY',
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'food',
            'notes' => 'テスト経費',
            'payer_member_id' => $this->member->id,
            'member_ids' => [$this->member->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan), $data);

        $response->assertRedirect(route('travel-plans.show', $this->travelPlan));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'description' => '食事代',
            'amount' => 3000,
            'currency' => 'JPY',
            'category' => 'food',
            'notes' => 'テスト経費',
            'is_settled' => false,
        ]);

        $expense = Expense::where('description', '食事代')->first();
        
        $this->assertDatabaseHas('expense_member', [
            'expense_id' => $expense->id,
            'member_id' => $this->member->id,
            'share_amount' => 3000, // 一人なので全額
            'is_paid' => true, // 支払者は支払い済み
        ]);
    }

    /**
     * 経費の作成時のバリデーションテスト
     */
    public function test_expense_validation(): void
    {
        $data = [
            'description' => '', // 必須項目を空に
            'amount' => -100, // 負の値
            'currency' => 'INVALID', // 無効な通貨コード
            'expense_date' => '', // 必須項目を空に
            'payer_member_id' => 999, // 存在しないメンバーID
            'member_ids' => [], // 空の配列
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan), $data);

        $response->assertSessionHasErrors(['description', 'amount', 'currency', 'expense_date', 'payer_member_id', 'member_ids']);
    }

    /**
     * 経費詳細ページのテスト
     */
    public function test_expense_show_page_can_be_rendered(): void
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
        ]);

        $expense->members()->attach($this->member->id, [
            'share_amount' => $expense->amount,
            'is_paid' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('expenses.show', $expense));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.show');
        $response->assertViewHas('expense');
        $response->assertSee($expense->description);
        $response->assertSee(number_format($expense->amount));
    }

    /**
     * 経費編集ページのテスト
     */
    public function test_expense_edit_page_can_be_rendered(): void
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
        ]);

        $expense->members()->attach($this->member->id, [
            'share_amount' => $expense->amount,
            'is_paid' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('expenses.edit', $expense));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.edit');
        $response->assertViewHas('expense');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('branchGroups');
        $response->assertViewHas('members');
        $response->assertViewHas('selectedMemberIds');
    }

    /**
     * 経費の更新テスト
     */
    public function test_expense_can_be_updated(): void
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'description' => '食事代',
            'amount' => 3000,
            'currency' => 'JPY',
            'category' => 'food',
        ]);

        $expense->members()->attach($this->member->id, [
            'share_amount' => $expense->amount,
            'is_paid' => true,
        ]);

        $data = [
            'description' => '夕食代（更新）',
            'amount' => 5000,
            'currency' => 'JPY',
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'food',
            'notes' => '更新テスト',
            'payer_member_id' => $this->member->id,
            'member_ids' => [$this->member->id],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('expenses.update', $expense), $data);

        $response->assertRedirect(route('expenses.show', $expense));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => '夕食代（更新）',
            'amount' => 5000,
            'notes' => '更新テスト',
        ]);

        $this->assertDatabaseHas('expense_member', [
            'expense_id' => $expense->id,
            'member_id' => $this->member->id,
            'share_amount' => 5000, // 更新された金額
        ]);
    }

    /**
     * 経費の削除テスト
     */
    public function test_expense_can_be_deleted(): void
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
        ]);

        $expense->members()->attach($this->member->id, [
            'share_amount' => $expense->amount,
            'is_paid' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('travel-plans.show', $this->travelPlan));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('expenses', [
            'id' => $expense->id,
        ]);
    }

    /**
     * 複数メンバーで経費を分担するテスト
     */
    public function test_expense_can_be_shared_among_multiple_members(): void
    {
        // 追加のメンバーを作成
        $member2 = Member::factory()->create([
            'group_id' => $this->coreGroup->id,
        ]);
        
        $member3 = Member::factory()->create([
            'group_id' => $this->coreGroup->id,
        ]);

        $data = [
            'description' => 'グループ食事代',
            'amount' => 9000,
            'currency' => 'JPY',
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'food',
            'notes' => 'テスト経費',
            'payer_member_id' => $this->member->id,
            'member_ids' => [$this->member->id, $member2->id, $member3->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan), $data);

        $response->assertRedirect(route('travel-plans.show', $this->travelPlan));
        $response->assertSessionHas('success');

        $expense = Expense::where('description', 'グループ食事代')->first();
        
        // 各メンバーの分担金額が正しいか確認（9000円÷3人=3000円）
        $this->assertDatabaseHas('expense_member', [
            'expense_id' => $expense->id,
            'member_id' => $this->member->id,
            'share_amount' => 3000,
            'is_paid' => true, // 支払者は支払い済み
        ]);
        
        $this->assertDatabaseHas('expense_member', [
            'expense_id' => $expense->id,
            'member_id' => $member2->id,
            'share_amount' => 3000,
            'is_paid' => false, // 支払者以外は未払い
        ]);
        
        $this->assertDatabaseHas('expense_member', [
            'expense_id' => $expense->id,
            'member_id' => $member3->id,
            'share_amount' => 3000,
            'is_paid' => false, // 支払者以外は未払い
        ]);
    }

    /**
     * 旅行計画詳細ページに経費セクションが表示されるかのテスト
     */
    public function test_travel_plan_detail_shows_expense_section(): void
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'payer_member_id' => $this->member->id,
            'description' => 'テスト経費',
            'amount' => 5000,
            'currency' => 'JPY',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.show', $this->travelPlan));

        $response->assertStatus(200);
        $response->assertSee('経費');
        $response->assertSee('テスト経費');
        $response->assertSee('5,000');
        $response->assertSee('JPY');
    }

    /**
     * 旅行計画詳細ページから経費追加ページへのリンクが機能するかのテスト
     */
    public function test_travel_plan_detail_has_link_to_create_expense(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.show', $this->travelPlan));

        $response->assertStatus(200);
        $response->assertSee('経費を追加');
        $response->assertSee(route('travel-plans.expenses.create', $this->travelPlan));
    }
}
