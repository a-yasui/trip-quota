<?php

namespace Tests\Unit\TripQuota;

use App\Models\Expense;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\Expense\ExpenseRepositoryInterface;
use TripQuota\Expense\ExpenseService;
use TripQuota\Member\MemberRepositoryInterface;

class ExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExpenseService $service;

    private ExpenseRepositoryInterface $expenseRepository;

    private MemberRepositoryInterface $memberRepository;

    private User $user;

    private TravelPlan $travelPlan;

    private Member $member;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expenseRepository = $this->createMock(ExpenseRepositoryInterface::class);
        $this->memberRepository = $this->createMock(MemberRepositoryInterface::class);
        $this->service = new ExpenseService($this->expenseRepository, $this->memberRepository);

        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
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

    public function test_create_expense_successfully()
    {
        $expenseData = [
            'title' => 'ランチ代',
            'description' => 'レストランでの昼食',
            'amount' => 5000,
            'currency' => 'JPY',
            'expense_date' => '2024-07-02',
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ];

        $expectedExpense = Expense::factory()->make([
            'id' => 1,
            'title' => 'ランチ代',
        ]);

        $this->memberRepository
            ->expects($this->exactly(2))
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->expenseRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($expectedExpense);

        $result = $this->service->createExpense($this->travelPlan, $this->user, $expenseData);

        $this->assertEquals($expectedExpense, $result);
    }

    public function test_create_expense_fails_with_invalid_dates()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('費用日付は旅行開始日以降である必要があります。');

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $expenseData = [
            'title' => 'テスト費用',
            'amount' => 1000,
            'expense_date' => '2024-06-30', // 旅行期間外
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ];

        $this->service->createExpense($this->travelPlan, $this->user, $expenseData);
    }

    public function test_create_expense_fails_with_zero_amount()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('金額は0より大きい値である必要があります。');

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $expenseData = [
            'title' => 'テスト費用',
            'amount' => 0, // 無効な金額
            'expense_date' => '2024-07-02',
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ];

        $this->service->createExpense($this->travelPlan, $this->user, $expenseData);
    }

    public function test_update_expense_successfully()
    {
        $expense = Expense::factory()->make([
            'id' => 1,
        ]);
        $expense->travelPlan = $this->travelPlan;

        $updateData = [
            'title' => '更新された費用',
            'description' => null,
            'amount' => 3000,
            'currency' => 'JPY',
            'expense_date' => '2024-07-03',
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
        ];

        $expectedExpense = Expense::factory()->make([
            'id' => 1,
            'title' => '更新された費用',
        ]);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->expenseRepository
            ->expects($this->once())
            ->method('update')
            ->with($expense, [
                'group_id' => $this->group->id,
                'paid_by_member_id' => $this->member->id,
                'title' => '更新された費用',
                'description' => null,
                'amount' => 3000,
                'currency' => 'JPY',
                'expense_date' => '2024-07-03',
            ])
            ->willReturn($expectedExpense);

        $result = $this->service->updateExpense($expense, $this->user, $updateData);

        $this->assertEquals($expectedExpense, $result);
    }


    public function test_delete_expense_successfully()
    {
        $expense = Expense::factory()->make([
            'id' => 1,
        ]);
        $expense->travelPlan = $this->travelPlan;

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->expenseRepository
            ->expects($this->once())
            ->method('delete')
            ->with($expense)
            ->willReturn(true);

        $result = $this->service->deleteExpense($expense, $this->user);

        $this->assertTrue($result);
    }


    public function test_get_expenses_for_travel_plan()
    {
        $expense1 = Expense::factory()->make(['id' => 1, 'title' => '費用A']);
        $expense2 = Expense::factory()->make(['id' => 2, 'title' => '費用B']);
        $expectedExpenses = new \Illuminate\Database\Eloquent\Collection([
            $expense1, $expense2,
        ]);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->expenseRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->with($this->travelPlan)
            ->willReturn($expectedExpenses);

        $result = $this->service->getExpensesForTravelPlan($this->travelPlan, $this->user);

        $this->assertEquals($expectedExpenses, $result);
    }


    public function test_calculate_split_amounts()
    {
        // 実際のEloquentモデルを使用してテスト
        $member1 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー1',
        ]);
        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー2',
        ]);

        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 5000,
        ]);

        // メンバーを費用に関連付け
        $expense->members()->attach([
            $member1->id => [
                'is_participating' => true,
                'amount' => null,
                'is_confirmed' => true,
            ],
            $member2->id => [
                'is_participating' => true,
                'amount' => 3000, // カスタム金額
                'is_confirmed' => false,
            ],
        ]);

        $result = $this->service->calculateSplitAmounts($expense);

        $this->assertCount(2, $result);

        // カスタム金額設定済みメンバー（member2）が先に配列に追加される
        $this->assertEquals($member2->id, $result[0]['member']->id);
        $this->assertEquals(3000, $result[0]['amount']); // カスタム金額
        $this->assertEquals(0, $result[0]['is_confirmed']); // データベースでは1/0で保存される

        // 残り金額から計算されるメンバー（member1）
        $this->assertEquals($member1->id, $result[1]['member']->id);
        $this->assertEquals(2000, $result[1]['amount']); // 残り金額 (5000 - 3000 = 2000)
        $this->assertEquals(1, $result[1]['is_confirmed']); // データベースでは1/0で保存される
    }

    public function test_calculate_split_amounts_with_error_when_custom_amounts_exceed_total()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('カスタム金額の合計が総金額を超えています。');

        $member1 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー1',
        ]);
        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー2',
        ]);

        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 5000,
        ]);

        // カスタム金額の合計が総金額を超える場合
        $expense->members()->attach([
            $member1->id => [
                'is_participating' => true,
                'amount' => 3000,
                'is_confirmed' => true,
            ],
            $member2->id => [
                'is_participating' => true,
                'amount' => 3000, // 合計6000円で総金額5000円を超える
                'is_confirmed' => false,
            ],
        ]);

        $this->service->calculateSplitAmounts($expense);
    }

    public function test_calculate_split_amounts_with_multiple_members_no_custom_amounts()
    {
        $member1 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー1',
        ]);
        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー2',
        ]);
        $member3 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー3',
        ]);

        $expense = Expense::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $this->group->id,
            'paid_by_member_id' => $this->member->id,
            'amount' => 9000,
        ]);

        // 全員にカスタム金額なし
        $expense->members()->attach([
            $member1->id => [
                'is_participating' => true,
                'amount' => null,
                'is_confirmed' => true,
            ],
            $member2->id => [
                'is_participating' => true,
                'amount' => null,
                'is_confirmed' => false,
            ],
            $member3->id => [
                'is_participating' => true,
                'amount' => null,
                'is_confirmed' => false,
            ],
        ]);

        $result = $this->service->calculateSplitAmounts($expense);

        $this->assertCount(3, $result);
        // 全員3000円ずつ
        foreach ($result as $split) {
            $this->assertEquals(3000, $split['amount']);
        }
    }

    public function test_validate_split_amounts_with_custom_amounts_exceeding_total()
    {
        // バリデーションメソッドを直接テスト
        $memberAssignments = [
            [
                'member_id' => 1,
                'is_participating' => true,
                'amount' => 3000,
            ],
            [
                'member_id' => 2,
                'is_participating' => true,
                'amount' => 3000,
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('個別金額の合計が総金額を超えています。');

        // リフレクションを使用してprivateメソッドを直接テスト
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateSplitAmounts');
        $method->setAccessible(true);

        $method->invoke($this->service, 5000, $memberAssignments);
    }

    public function test_validate_split_amounts_with_all_members_having_custom_amounts_not_matching_total()
    {
        // バリデーションメソッドを直接テスト
        $memberAssignments = [
            [
                'member_id' => 1,
                'is_participating' => true,
                'amount' => 2000,
            ],
            [
                'member_id' => 2,
                'is_participating' => true,
                'amount' => 2000,
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('全員に個別金額を設定する場合、合計は総金額と一致する必要があります。');

        // リフレクションを使用してprivateメソッドを直接テスト
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateSplitAmounts');
        $method->setAccessible(true);

        $method->invoke($this->service, 5000, $memberAssignments);
    }

    public function test_validate_split_amounts_with_valid_partial_custom_amounts()
    {
        // 正常なケースもテスト
        $memberAssignments = [
            [
                'member_id' => 1,
                'is_participating' => true,
                'amount' => 2000,
            ],
            [
                'member_id' => 2,
                'is_participating' => true,
                'amount' => null,  // 残り金額から計算される
            ],
        ];

        // リフレクションを使用してprivateメソッドを直接テスト
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateSplitAmounts');
        $method->setAccessible(true);

        // 例外が発生しないことを確認
        $method->invoke($this->service, 5000, $memberAssignments);

        // ここまで到達すれば成功
        $this->assertTrue(true);
    }
}
