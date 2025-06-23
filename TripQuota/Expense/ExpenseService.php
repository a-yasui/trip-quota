<?php

namespace TripQuota\Expense;

use App\Models\Expense;
use App\Models\Group;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use TripQuota\Member\MemberRepositoryInterface;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepository,
        private MemberRepositoryInterface $memberRepository
    ) {}

    public function createExpense(TravelPlan $travelPlan, User $user, array $data): Expense
    {
        $this->ensureUserCanManageExpenses($travelPlan, $user);
        $this->validateExpenseData($data, $travelPlan);

        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);
        $group = Group::find($data['group_id']);

        // グループが旅行プランに属することを確認
        if ($group->travel_plan_id !== $travelPlan->id) {
            throw new \Exception('指定されたグループは、この旅行プランに属していません。');
        }

        return DB::transaction(function () use ($travelPlan, $data) {
            $expense = $this->expenseRepository->create([
                'travel_plan_id' => $travelPlan->id,
                'group_id' => $data['group_id'],
                'paid_by_member_id' => $data['paid_by_member_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'JPY',
                'expense_date' => $data['expense_date'],
            ]);

            // メンバーを費用に割り当て
            if (isset($data['member_assignments']) && is_array($data['member_assignments'])) {
                $this->assignMembersToExpense($expense, $data['member_assignments'], $travelPlan);
            }

            return $expense;
        });
    }

    public function updateExpense(Expense $expense, User $user, array $data): Expense
    {
        $this->ensureUserCanEditExpense($expense, $user);

        $this->validateExpenseData($data, $expense->travelPlan);

        return DB::transaction(function () use ($expense, $data) {
            $expense = $this->expenseRepository->update($expense, [
                'group_id' => $data['group_id'],
                'paid_by_member_id' => $data['paid_by_member_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'JPY',
                'expense_date' => $data['expense_date'],
            ]);

            // メンバー割り当ての更新
            if (isset($data['member_assignments']) && is_array($data['member_assignments'])) {
                $this->assignMembersToExpense($expense, $data['member_assignments'], $expense->travelPlan);
            }

            return $expense;
        });
    }

    public function deleteExpense(Expense $expense, User $user): bool
    {
        $this->ensureUserCanEditExpense($expense, $user);

        return $this->expenseRepository->delete($expense);
    }

    public function getExpensesForTravelPlan(TravelPlan $travelPlan, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewExpenses($travelPlan, $user);

        return $this->expenseRepository->findByTravelPlan($travelPlan);
    }

    public function getExpensesForGroup(Group $group, User $user): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureUserCanViewExpenses($group->travelPlan, $user);

        return $this->expenseRepository->findByGroup($group);
    }

    public function autoParticipateCurrentUser(Expense $expense, User $user): void
    {

        // ユーザーのメンバー情報を取得
        $member = $this->memberRepository->findByTravelPlanAndUser(
            $expense->travelPlan,
            $user
        );

        if (! $member) {
            return;
        }

        // 既に参加済みかチェック
        $existingPivot = $expense->members()->where('members.id', $member->id)->first();

        if ($existingPivot) {
            // 既に参加しているが、is_confirmとis_participatingがfalseの場合は自動確認
            if (! $existingPivot->pivot->is_participating || ! $existingPivot->pivot->is_confirmed) {
                $expense->members()->updateExistingPivot($member->id, [
                    'is_participating' => true,
                    'is_confirmed' => true,
                ]);
            }
        } else {
            // まだ参加していない場合は自動参加
            $expense->members()->attach($member->id, [
                'is_participating' => true,
                'is_confirmed' => true,
                'amount' => null,
            ]);
        }
    }

    public function calculateSplitAmounts(Expense $expense): array
    {
        $participatingMembers = $expense->members()
            ->wherePivot('is_participating', true)
            ->get();

        if ($participatingMembers->isEmpty()) {
            return [];
        }

        $totalAmount = $expense->amount;
        $memberCount = $participatingMembers->count();

        // カスタム金額が設定されているメンバーの合計を計算
        $customAmountTotal = 0;
        $membersWithCustomAmount = [];
        $membersWithoutCustomAmount = [];

        foreach ($participatingMembers as $member) {
            $customAmount = $member->pivot->amount;
            if ($customAmount !== null && $customAmount > 0) {
                $customAmountTotal += $customAmount;
                $membersWithCustomAmount[] = $member;
            } else {
                $membersWithoutCustomAmount[] = $member;
            }
        }

        // 残り金額を計算
        $remainingAmount = $totalAmount - $customAmountTotal;
        $remainingMemberCount = count($membersWithoutCustomAmount);

        // 残り金額が負の場合はエラー
        if ($remainingAmount < 0) {
            throw new \Exception('カスタム金額の合計が総金額を超えています。');
        }

        // 残りメンバーの一人当たり金額を計算
        $remainingSplitAmount = $remainingMemberCount > 0 ? $remainingAmount / $remainingMemberCount : 0;

        $splits = [];

        // カスタム金額設定済みメンバー
        foreach ($membersWithCustomAmount as $member) {
            $splits[] = [
                'member' => $member,
                'amount' => $member->pivot->amount,
                'is_confirmed' => $member->pivot->is_confirmed,
            ];
        }

        // カスタム金額未設定メンバー
        foreach ($membersWithoutCustomAmount as $member) {
            $splits[] = [
                'member' => $member,
                'amount' => $remainingSplitAmount,
                'is_confirmed' => $member->pivot->is_confirmed,
            ];
        }

        return $splits;
    }

    public function updateExpenseSplits(Expense $expense, User $user, array $splits): void
    {

        // ユーザーが管理権限を持つかチェック
        $this->ensureUserCanManageExpenses($expense->travelPlan, $user);

        // 分割金額の合計が総金額と一致するかチェック
        $totalSplitAmount = array_sum(array_column($splits, 'amount'));
        if (abs($totalSplitAmount - $expense->amount) > 0.01) {
            throw new \Exception('分割金額の合計が総金額と一致しません。');
        }

        // 各メンバーの分割金額を更新
        foreach ($splits as $split) {
            $expense->members()->updateExistingPivot($split['member_id'], [
                'amount' => $split['amount'],
            ]);
        }
    }

    private function ensureUserCanViewExpenses(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランの費用を表示する権限がありません。');
        }
    }

    private function ensureUserCanManageExpenses(TravelPlan $travelPlan, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('費用を管理する権限がありません。');
        }
    }

    private function ensureUserCanEditExpense(Expense $expense, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($expense->travelPlan, $user);

        if (! $member || ! $member->is_confirmed) {
            throw new \Exception('この費用を編集する権限がありません。');
        }

        // 作成者または管理者のみ編集可能
        // 現在は全ての確認済みメンバーが編集可能
    }

    private function validateExpenseData(array $data, TravelPlan $travelPlan): void
    {
        $expenseDate = \Carbon\Carbon::parse($data['expense_date']);

        // 費用日付が旅行期間内かチェック
        if ($expenseDate->lt($travelPlan->departure_date)) {
            throw new \Exception('費用日付は旅行開始日以降である必要があります。');
        }

        if ($travelPlan->return_date && $expenseDate->gt($travelPlan->return_date)) {
            throw new \Exception('費用日付は旅行終了日以前である必要があります。');
        }

        // 金額が正の値かチェック
        if ($data['amount'] <= 0) {
            throw new \Exception('金額は0より大きい値である必要があります。');
        }
    }

    private function assignMembersToExpense(Expense $expense, array $memberAssignments, TravelPlan $travelPlan): void
    {
        // 全メンバーIDが旅行プランに属することを確認
        $validMembers = $this->memberRepository->findByTravelPlan($travelPlan)
            ->where('is_confirmed', true)
            ->pluck('id')
            ->toArray();

        // is_participatingキーが存在しない場合はfalseとして扱う
        $normalizedAssignments = [];
        foreach ($memberAssignments as $assignment) {
            if (! in_array($assignment['member_id'], $validMembers)) {
                throw new \Exception('無効なメンバーIDが含まれています。');
            }

            $normalizedAssignments[] = [
                'member_id' => $assignment['member_id'],
                'is_participating' => $assignment['is_participating'] ?? false,
                'amount' => $assignment['amount'] ?? null,
            ];
        }

        // 分割金額の合計検証
        $this->validateSplitAmounts($expense->amount, $normalizedAssignments);

        $this->expenseRepository->assignMembers($expense, $normalizedAssignments);
    }

    private function validateSplitAmounts(float $totalAmount, array $memberAssignments): void
    {
        $customAmountTotal = 0;
        $participatingCount = 0;
        $customAmountCount = 0;

        foreach ($memberAssignments as $assignment) {
            // is_participatingが存在しない場合はfalseとして扱う
            $isParticipating = $assignment['is_participating'] ?? false;

            if ($isParticipating) {
                $participatingCount++;

                if (isset($assignment['amount']) && $assignment['amount'] !== null && $assignment['amount'] > 0) {
                    $customAmountTotal += $assignment['amount'];
                    $customAmountCount++;
                }
            }
        }

        // カスタム金額が設定されたメンバーがいる場合の検証
        if ($customAmountCount > 0) {
            // カスタム金額の合計が総金額を超える場合はエラー
            if ($customAmountTotal > $totalAmount) {
                throw new \Exception('個別金額の合計が総金額を超えています。');
            }

            // 全員にカスタム金額が設定されている場合、合計が総金額と一致する必要がある
            if ($customAmountCount === $participatingCount && $customAmountTotal != $totalAmount) {
                throw new \Exception('全員に個別金額を設定する場合、合計は総金額と一致する必要があります。');
            }
        }
    }
}
