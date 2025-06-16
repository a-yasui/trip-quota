<?php

namespace Tests\Unit\TripQuota;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TravelPlan;
use App\Models\User;
use App\Models\Member;
use App\Models\Group;
use App\Models\Expense;
use App\Models\ExpenseSettlement;
use TripQuota\Settlement\SettlementService;
use TripQuota\Settlement\SettlementRepositoryInterface;
use TripQuota\Expense\ExpenseRepositoryInterface;
use TripQuota\Member\MemberRepositoryInterface;

class SettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettlementService $service;
    private SettlementRepositoryInterface $settlementRepository;
    private ExpenseRepositoryInterface $expenseRepository;
    private MemberRepositoryInterface $memberRepository;
    private User $user;
    private TravelPlan $travelPlan;
    private Member $member;
    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settlementRepository = $this->createMock(SettlementRepositoryInterface::class);
        $this->expenseRepository = $this->createMock(ExpenseRepositoryInterface::class);
        $this->memberRepository = $this->createMock(MemberRepositoryInterface::class);
        $this->service = new SettlementService(
            $this->settlementRepository,
            $this->expenseRepository,
            $this->memberRepository
        );
        
        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create();
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
        $this->group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);
    }

    public function test_calculate_settlements_with_no_expenses()
    {
        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->expenseRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->with($this->travelPlan)
            ->willReturn(new Collection());

        $result = $this->service->calculateSettlements($this->travelPlan, $this->user);

        $this->assertEquals([], $result);
    }

    public function test_calculate_settlements_with_balanced_expenses()
    {
        // 2人のメンバーがそれぞれ1000円払い、お互いに500円ずつ分担する場合
        $member1 = Member::factory()->make(['id' => 1, 'name' => 'メンバー1']);
        $member2 = Member::factory()->make(['id' => 2, 'name' => 'メンバー2']);

        $expense1 = Expense::factory()->make([
            'id' => 1,
            'paid_by_member_id' => 1,
            'amount' => 1000,
            'currency' => 'JPY',
            'is_split_confirmed' => true,
        ]);

        $expense2 = Expense::factory()->make([
            'id' => 2,
            'paid_by_member_id' => 2,
            'amount' => 1000,
            'currency' => 'JPY',
            'is_split_confirmed' => true,
        ]);

        // モック設定
        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $expenses = new Collection([$expense1, $expense2]);
        $this->expenseRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->with($this->travelPlan)
            ->willReturn($expenses);

        // 各費用のメンバー関係をモック
        $expense1->members = function() use ($member1, $member2) {
            $query = $this->createMock(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
            $query->method('wherePivot')->willReturnSelf();
            $query->method('get')->willReturn(new Collection([
                $this->createMemberWithPivot($member1, true, null),
                $this->createMemberWithPivot($member2, true, null),
            ]));
            return $query;
        };

        $expense2->members = function() use ($member1, $member2) {
            $query = $this->createMock(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
            $query->method('wherePivot')->willReturnSelf();
            $query->method('get')->willReturn(new Collection([
                $this->createMemberWithPivot($member1, true, null),
                $this->createMemberWithPivot($member2, true, null),
            ]));
            return $query;
        };

        $result = $this->service->calculateSettlements($this->travelPlan, $this->user);

        // バランスが取れているので精算は不要
        $this->assertEquals([], $result);
    }

    public function test_calculate_settlements_with_imbalanced_expenses()
    {
        $member1 = Member::factory()->make(['id' => 1, 'name' => 'メンバー1']);
        $member2 = Member::factory()->make(['id' => 2, 'name' => 'メンバー2']);

        // メンバー1が1500円、メンバー2が500円を支払い、それぞれ1000円ずつ分担
        $expense1 = Expense::factory()->make([
            'id' => 1,
            'paid_by_member_id' => 1,
            'amount' => 1500,
            'currency' => 'JPY',
            'is_split_confirmed' => true,
        ]);

        $expense2 = Expense::factory()->make([
            'id' => 2,
            'paid_by_member_id' => 2,
            'amount' => 500,
            'currency' => 'JPY',
            'is_split_confirmed' => true,
        ]);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->willReturn($this->member);

        $expenses = new Collection([$expense1, $expense2]);
        $this->expenseRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->willReturn($expenses);

        // 各費用のメンバー関係をモック
        $expense1->members = function() use ($member1, $member2) {
            $query = $this->createMock(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
            $query->method('wherePivot')->willReturnSelf();
            $query->method('get')->willReturn(new Collection([
                $this->createMemberWithPivot($member1, true, null),
                $this->createMemberWithPivot($member2, true, null),
            ]));
            return $query;
        };

        $expense2->members = function() use ($member1, $member2) {
            $query = $this->createMock(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
            $query->method('wherePivot')->willReturnSelf();
            $query->method('get')->willReturn(new Collection([
                $this->createMemberWithPivot($member1, true, null),
                $this->createMemberWithPivot($member2, true, null),
            ]));
            return $query;
        };

        $result = $this->service->calculateSettlements($this->travelPlan, $this->user);

        // メンバー2がメンバー1に500円支払う必要がある
        $this->assertArrayHasKey('JPY', $result);
        $this->assertCount(1, $result['JPY']);
        $this->assertEquals(2, $result['JPY'][0]['payer_member_id']);
        $this->assertEquals(1, $result['JPY'][0]['payee_member_id']);
        $this->assertEquals(500, $result['JPY'][0]['amount']);
    }

    public function test_generate_settlement_proposal_creates_settlements()
    {
        $this->memberRepository
            ->expects($this->exactly(2))
            ->method('findByTravelPlanAndUser')
            ->willReturn($this->member);

        $this->expenseRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->willReturn(new Collection());

        $this->settlementRepository
            ->expects($this->once())
            ->method('clearByTravelPlan')
            ->with($this->travelPlan);

        $result = $this->service->generateSettlementProposal($this->travelPlan, $this->user);

        $this->assertEquals([], $result);
    }

    public function test_mark_settlement_as_completed()
    {
        $settlement = ExpenseSettlement::factory()->make([
            'id' => 1,
            'is_settled' => false,
        ]);
        $settlement->travelPlan = $this->travelPlan;

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->willReturn($this->member);

        $updatedSettlement = ExpenseSettlement::factory()->make([
            'id' => 1,
            'is_settled' => true,
        ]);

        $this->settlementRepository
            ->expects($this->once())
            ->method('markAsSettled')
            ->with($settlement)
            ->willReturn($updatedSettlement);

        $result = $this->service->markSettlementAsCompleted($settlement, $this->user);

        $this->assertTrue($result->is_settled);
    }

    public function test_mark_settlement_as_completed_fails_when_already_settled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('この精算は既に完了済みです。');

        $settlement = ExpenseSettlement::factory()->make([
            'is_settled' => true,
        ]);
        $settlement->travelPlan = $this->travelPlan;

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->willReturn($this->member);

        $this->service->markSettlementAsCompleted($settlement, $this->user);
    }

    public function test_get_settlement_statistics()
    {
        $settlements = new Collection([
            ExpenseSettlement::factory()->make([
                'currency' => 'JPY',
                'amount' => 1000,
                'is_settled' => false,
            ]),
            ExpenseSettlement::factory()->make([
                'currency' => 'JPY',
                'amount' => 2000,
                'is_settled' => true,
            ]),
            ExpenseSettlement::factory()->make([
                'currency' => 'USD',
                'amount' => 50,
                'is_settled' => false,
            ]),
        ]);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->willReturn($this->member);

        $this->settlementRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->with($this->travelPlan)
            ->willReturn($settlements);

        $result = $this->service->getSettlementStatistics($this->travelPlan, $this->user);

        $this->assertEquals(3, $result['total_settlements']);
        $this->assertEquals(1, $result['completed_settlements']);
        $this->assertEquals(2, $result['pending_settlements']);
        
        $this->assertArrayHasKey('JPY', $result['by_currency']);
        $this->assertEquals(3000, $result['by_currency']['JPY']['total_amount']);
        $this->assertEquals(2000, $result['by_currency']['JPY']['completed_amount']);
        $this->assertEquals(1000, $result['by_currency']['JPY']['pending_amount']);
        
        $this->assertArrayHasKey('USD', $result['by_currency']);
        $this->assertEquals(50, $result['by_currency']['USD']['total_amount']);
    }

    public function test_unauthorized_user_cannot_view_settlements()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('この旅行プランの精算情報を表示する権限がありません。');

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->willReturn(null);

        $this->service->getSettlementsForTravelPlan($this->travelPlan, $this->user);
    }

    public function test_unconfirmed_member_cannot_manage_settlements()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('精算を管理する権限がありません。');

        $unconfirmedMember = Member::factory()->make([
            'is_confirmed' => false,
        ]);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->willReturn($unconfirmedMember);

        $this->service->generateSettlementProposal($this->travelPlan, $this->user);
    }

    private function createMemberWithPivot($member, $isParticipating, $amount)
    {
        $pivot = new \stdClass();
        $pivot->amount = $amount;

        $memberWithPivot = clone $member;
        $memberWithPivot->pivot = $pivot;

        return $memberWithPivot;
    }
}