<?php

namespace TripQuota\Settlement;

use App\Models\ExpenseSettlement;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use TripQuota\Expense\ExpenseRepositoryInterface;
use TripQuota\Member\MemberRepositoryInterface;

class SettlementService
{
    public function __construct(
        private SettlementRepositoryInterface $settlementRepository,
        private ExpenseRepositoryInterface $expenseRepository,
        private MemberRepositoryInterface $memberRepository
    ) {}

    /**
     * 旅行プランの精算計算を実行
     */
    public function calculateSettlements(TravelPlan $travelPlan, User $user): array
    {
        $this->ensureUserCanViewSettlements($travelPlan, $user);

        // 全ての費用を対象
        $expenses = $this->expenseRepository->findByTravelPlan($travelPlan)
    ;

        if ($expenses->isEmpty()) {
            return [];
        }

        // 通貨別に精算計算を実行
        $settlementsByCurrency = [];
        $expensesByCurrency = $expenses->groupBy('currency');

        foreach ($expensesByCurrency as $currency => $currencyExpenses) {
            $settlements = $this->calculateSettlementsForCurrency($travelPlan, $currencyExpenses, $currency);
            if (! empty($settlements)) {
                $settlementsByCurrency[$currency] = $settlements;
            }
        }

        return $settlementsByCurrency;
    }

    /**
     * 特定通貨の精算計算
     */
    private function calculateSettlementsForCurrency(TravelPlan $travelPlan, Collection $expenses, string $currency): array
    {
        $memberBalances = [];

        // 各メンバーの収支を計算
        foreach ($expenses as $expense) {
            $splitAmounts = $this->calculateExpenseSplitAmounts($expense);

            // 支払った人の収支（プラス）
            $payerId = $expense->paid_by_member_id;
            if (! isset($memberBalances[$payerId])) {
                $memberBalances[$payerId] = 0;
            }
            $memberBalances[$payerId] += $expense->amount;

            // 分割対象者の収支（マイナス）
            foreach ($splitAmounts as $split) {
                $memberId = $split['member']->id;
                if (! isset($memberBalances[$memberId])) {
                    $memberBalances[$memberId] = 0;
                }
                $memberBalances[$memberId] -= $split['amount'];
            }
        }

        // 収支を精算する最適化計算
        return $this->optimizeSettlements($travelPlan, $memberBalances, $currency);
    }

    /**
     * 費用の分割金額を計算
     */
    private function calculateExpenseSplitAmounts($expense): array
    {
        $participatingMembers = $expense->members()
            ->wherePivot('is_participating', true)
            ->get();

        if ($participatingMembers->isEmpty()) {
            return [];
        }

        $totalAmount = $expense->amount;
        $memberCount = $participatingMembers->count();
        $defaultSplitAmount = $totalAmount / $memberCount;

        $splits = [];
        foreach ($participatingMembers as $member) {
            $customAmount = $member->pivot->amount;
            $splits[] = [
                'member' => $member,
                'amount' => $customAmount ?? $defaultSplitAmount,
            ];
        }

        return $splits;
    }

    /**
     * 精算の最適化計算（債務の最小化）
     */
    private function optimizeSettlements(TravelPlan $travelPlan, array $memberBalances, string $currency): array
    {
        // ゼロの残高を除去
        $memberBalances = array_filter($memberBalances, function ($balance) {
            return abs($balance) >= 0.01; // 1円未満は誤差として無視
        });

        if (empty($memberBalances)) {
            return [];
        }

        // 債権者（受け取る人）と債務者（支払う人）を分離
        $creditors = []; // プラス残高（お金を受け取る人）
        $debtors = [];   // マイナス残高（お金を支払う人）

        foreach ($memberBalances as $memberId => $balance) {
            if ($balance > 0) {
                $creditors[$memberId] = $balance;
            } elseif ($balance < 0) {
                $debtors[$memberId] = abs($balance);
            }
        }

        // 精算計算
        $settlements = [];

        // 債務者から債権者への精算を計算
        foreach ($debtors as $debtorId => $debtAmount) {
            foreach ($creditors as $creditorId => $creditAmount) {
                if ($debtAmount <= 0 || $creditAmount <= 0) {
                    continue;
                }

                $settlementAmount = min($debtAmount, $creditAmount);

                if ($settlementAmount >= 0.01) { // 1円以上の精算のみ記録
                    $settlements[] = [
                        'travel_plan_id' => $travelPlan->id,
                        'payer_member_id' => $debtorId,
                        'payee_member_id' => $creditorId,
                        'amount' => round($settlementAmount, 2),
                        'currency' => $currency,
                    ];

                    $debtors[$debtorId] -= $settlementAmount;
                    $creditors[$creditorId] -= $settlementAmount;
                }
            }
        }

        return $settlements;
    }

    /**
     * 精算提案を生成・保存
     */
    public function generateSettlementProposal(TravelPlan $travelPlan, User $user): array
    {
        $this->ensureUserCanManageSettlements($travelPlan, $user);

        return DB::transaction(function () use ($travelPlan, $user) {
            // 既存の未精算情報をクリア
            $this->settlementRepository->clearByTravelPlan($travelPlan);

            // 新しい精算計算
            $settlementsByCurrency = $this->calculateSettlements($travelPlan, $user);

            $allSettlements = [];
            foreach ($settlementsByCurrency as $currency => $settlements) {
                if (! empty($settlements)) {
                    $created = $this->settlementRepository->createMultiple($settlements);
                    $allSettlements[$currency] = $created;
                }
            }

            return $allSettlements;
        });
    }

    /**
     * 精算完了の記録
     */
    public function markSettlementAsCompleted(ExpenseSettlement $settlement, User $user): ExpenseSettlement
    {
        $this->ensureUserCanManageSettlements($settlement->travelPlan, $user);

        if ($settlement->settled_at !== null) {
            throw new \Exception('この精算は既に完了済みです。');
        }

        return $this->settlementRepository->markAsSettled($settlement);
    }

    /**
     * 旅行プランの精算情報を取得
     */
    public function getSettlementsForTravelPlan(TravelPlan $travelPlan, User $user): Collection
    {
        $this->ensureUserCanViewSettlements($travelPlan, $user);

        return $this->settlementRepository->findByTravelPlan($travelPlan);
    }

    /**
     * 未精算の精算情報を取得
     */
    public function getPendingSettlementsForTravelPlan(TravelPlan $travelPlan, User $user): Collection
    {
        $this->ensureUserCanViewSettlements($travelPlan, $user);

        return $this->settlementRepository->findPendingByTravelPlan($travelPlan);
    }

    /**
     * 精算統計情報を取得
     */
    public function getSettlementStatistics(TravelPlan $travelPlan, User $user): array
    {
        $this->ensureUserCanViewSettlements($travelPlan, $user);

        $settlements = $this->settlementRepository->findByTravelPlan($travelPlan);

        $statistics = [
            'total_settlements' => $settlements->count(),
            'completed_settlements' => $settlements->whereNotNull('settled_at')->count(),
            'pending_settlements' => $settlements->whereNull('settled_at')->count(),
            'by_currency' => [],
        ];

        // 通貨別統計
        $settlementsByCurrency = $settlements->groupBy('currency');
        foreach ($settlementsByCurrency as $currency => $currencySettlements) {
            $statistics['by_currency'][$currency] = [
                'total_amount' => $currencySettlements->sum('amount'),
                'completed_amount' => $currencySettlements->whereNotNull('settled_at')->sum('amount'),
                'pending_amount' => $currencySettlements->whereNull('settled_at')->sum('amount'),
                'count' => $currencySettlements->count(),
            ];
        }

        return $statistics;
    }

    /**
     * 旅行プランの未精算情報をクリア
     */
    public function clearByTravelPlan(TravelPlan $travelPlan): void
    {
        $this->settlementRepository->clearByTravelPlan($travelPlan);
    }

    private function ensureUserCanViewSettlements(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランの精算情報を表示する権限がありません。');
        }
    }

    private function ensureUserCanManageSettlements(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('精算を管理する権限がありません。');
        }
    }
}
