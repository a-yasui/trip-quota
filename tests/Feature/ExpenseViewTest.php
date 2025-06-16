<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseViewTest extends TestCase
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
            'plan_name' => 'テスト旅行プラン',
            'departure_date' => '2024-07-01',
            'return_date' => '2024-07-05',
        ]);
        
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'name' => 'テストメンバー',
            'is_confirmed' => true,
        ]);

        $this->group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'テストグループ',
        ]);
    }

    public function test_expense_index_view_renders_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.index');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('expenses');
        
        // 基本的なページ要素の確認
        $response->assertSee('費用管理');
        $response->assertSee('テスト旅行プラン');
        $response->assertSee('費用を追加');
        $response->assertSee('旅行プラン詳細に戻る');
    }

    public function test_expense_create_view_renders_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.create');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('members');
        $response->assertViewHas('groups');
        
        // フォーム要素の確認
        $response->assertSee('費用を追加');
        $response->assertSee('費用タイトル');
        $response->assertSee('説明・詳細');
        $response->assertSee('金額');
        $response->assertSee('通貨');
        $response->assertSee('費用日付');
        $response->assertSee('対象グループ');
        $response->assertSee('支払い者');
        $response->assertSee('分割対象メンバー');
        $response->assertSee('テストメンバー');
        $response->assertSee('テストグループ');
        
        // 通貨オプションの確認
        $response->assertSee('JPY (円)');
        $response->assertSee('USD (ドル)');
        $response->assertSee('EUR (ユーロ)');
        
        // 旅行期間の制約確認
        $response->assertSee('min="2024-07-01"');
        $response->assertSee('max="2024-07-05"');
    }

    public function test_expense_show_view_displays_details()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => 'ランチ代',
            'description' => 'レストランでの昼食',
            'amount' => 5000,
            'currency' => 'JPY',
            'expense_date' => '2024-07-02',
            'is_split_confirmed' => false,
        ]);

        $expense->members()->attach($this->member->id, [
            'is_participating' => true,
            'amount' => null,
            'is_confirmed' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.show', [$this->travelPlan->uuid, $expense->id]));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.show');
        $response->assertViewHas('expense');
        $response->assertViewHas('splitAmounts');
        
        // 費用詳細の確認
        $response->assertSee('ランチ代');
        $response->assertSee('レストランでの昼食');
        $response->assertSee('5,000 JPY');
        $response->assertSee('2024年7月2日');
        $response->assertSee('テストグループ');
        $response->assertSee('テストメンバー');
        $response->assertSee('未確定');
        
        // 分割詳細の確認
        $response->assertSee('分割詳細');
        $response->assertSee('未確認');
        $response->assertSee('参加を確認');
        
        // 編集・削除ボタンの確認（未確定の場合）
        $response->assertSee('編集');
        $response->assertSee('削除');
    }

    public function test_expense_show_view_confirmed_expense()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => '確定済み費用',
            'amount' => 3000,
            'currency' => 'JPY',
            'is_split_confirmed' => true, // 確定済み
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.show', [$this->travelPlan->uuid, $expense->id]));

        $response->assertStatus(200);
        $response->assertSee('確定済み費用');
        $response->assertSee('確定済み');
        
        // 確定済みなので編集・削除ボタンは表示されない
        $response->assertDontSee('編集');
        $response->assertDontSee('削除');
    }

    public function test_expense_edit_view_renders_correctly()
    {
        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'title' => '編集用費用',
            'description' => '編集前の説明',
            'amount' => 2000,
            'currency' => 'USD',
            'is_split_confirmed' => false,
        ]);

        $expense->members()->attach($this->member->id, [
            'is_participating' => true,
            'amount' => 1000,
            'is_confirmed' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.edit', [$this->travelPlan->uuid, $expense->id]));

        $response->assertStatus(200);
        $response->assertViewIs('expenses.edit');
        $response->assertViewHas('expense');
        $response->assertViewHas('members');
        $response->assertViewHas('groups');
        
        // フォーム要素と既存値の確認
        $response->assertSee('費用を編集');
        $response->assertSee('編集用費用');
        $response->assertSee('編集前の説明');
        $response->assertSee('value="2000"');
        $response->assertSee('USD');
        $response->assertSee('checked'); // メンバーが参加している
        $response->assertSee('value="1000"'); // カスタム金額
    }

    public function test_expense_index_empty_state()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('費用がありません');
        $response->assertSee('最初の費用を追加してみましょう。');
    }

    public function test_expense_index_statistics_display()
    {
        // 複数の費用を作成
        Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 1000,
            'currency' => 'JPY',
            'is_split_confirmed' => false,
        ]);

        Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 2000,
            'currency' => 'JPY',
            'is_split_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        
        // 統計情報の確認
        $response->assertSee('総費用');
        $response->assertSee('3,000 JPY');
        $response->assertSee('費用件数');
        $response->assertSee('2件');
        $response->assertSee('確定済み');
        $response->assertSee('1件');
    }

    public function test_expense_create_form_defaults()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        
        // デフォルト値の確認
        $response->assertSee('value="2024-07-01"'); // デフォルトの費用日付
        $response->assertSee('selected'); // JPYがデフォルト選択
        $response->assertSee('checked'); // メンバーがデフォルトで参加
    }

    public function test_expense_form_validation_errors_display()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.expenses.store', $this->travelPlan->uuid), [
                'title' => '', // 必須項目を空に
                'amount' => '',
                'currency' => '',
            ]);

        $response->assertSessionHasErrors(['title', 'amount', 'currency']);
        
        // エラーページのリダイレクト先を確認
        $followResponse = $this->followRedirects($response);
        $followResponse->assertSee('費用を追加');
    }

    public function test_expense_view_includes_master_layout()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('TripQuota'); // タイトルに含まれるアプリ名
    }

    public function test_expense_navigation_links()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        
        // ナビゲーションリンクの確認
        $backUrl = route('travel-plans.show', $this->travelPlan->uuid);
        $createUrl = route('travel-plans.expenses.create', $this->travelPlan->uuid);
        
        $response->assertSee($backUrl);
        $response->assertSee($createUrl);
    }

    public function test_expense_show_split_calculation_display()
    {
        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー2',
            'is_confirmed' => true,
        ]);

        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 6000,
            'currency' => 'JPY',
        ]);

        // 2人で分割
        $expense->members()->attach([
            $this->member->id => [
                'is_participating' => true,
                'amount' => null, // 自動計算
                'is_confirmed' => true,
            ],
            $member2->id => [
                'is_participating' => true,
                'amount' => 4000, // カスタム金額
                'is_confirmed' => false,
            ]
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.expenses.show', [$this->travelPlan->uuid, $expense->id]));

        $response->assertStatus(200);
        
        // 分割計算の確認
        $response->assertSee('3,000 JPY'); // 等分計算結果
        $response->assertSee('4,000 JPY'); // カスタム金額
        $response->assertSee('確認済み');
        $response->assertSee('未確認');
        
        // 分割計算サマリーの確認
        $response->assertSee('総金額');
        $response->assertSee('6,000 JPY');
        $response->assertSee('参加者数');
        $response->assertSee('2人');
        $response->assertSee('1人あたり');
        $response->assertSee('3,000 JPY');
    }
}