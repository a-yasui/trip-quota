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
                'is_split_confirmed' => false,
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

        // 確定済みの費用は編集不可
        if ($expense->is_split_confirmed) {
            throw new \Exception('確定済みの費用は編集できません。');
        }

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

        // 確定済みの費用は削除不可
        if ($expense->is_split_confirmed) {
            throw new \Exception('確定済みの費用は削除できません。');
        }

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

    public function confirmExpenseSplit(Expense $expense, User $user): Expense
    {
        $this->ensureUserCanEditExpense($expense, $user);

        if ($expense->is_split_confirmed) {
            throw new \Exception('この費用は既に確定済みです。');
        }

        // 全メンバーが確認済みかチェック
        $unconfirmedMembers = $expense->members()->wherePivot('is_confirmed', false)->count();
        if ($unconfirmedMembers > 0) {
            throw new \Exception('全メンバーの確認が完了していません。');
        }

        return $this->expenseRepository->confirmSplit($expense);
    }

    public function confirmMemberParticipation(Expense $expense, User $user): void
    {
        $member = $this->memberRepository->findByTravelPlanAndUser($expense->travelPlan, $user);

        if (! $member) {
            throw new \Exception('この旅行プランのメンバーではありません。');
        }

        // メンバーが費用に参加しているかチェック
        $expenseMember = $expense->members()->where('member_id', $member->id)->first();
        if (! $expenseMember) {
            throw new \Exception('この費用に参加していません。');
        }

        // 確認状態を更新
        $expense->members()->updateExistingPivot($member->id, ['is_confirmed' => true]);
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
        $splitAmount = $totalAmount / $memberCount;

        $splits = [];
        foreach ($participatingMembers as $member) {
            $customAmount = $member->pivot->amount;
            $splits[] = [
                'member' => $member,
                'amount' => $customAmount ?? $splitAmount,
                'is_confirmed' => $member->pivot->is_confirmed,
            ];
        }

        return $splits;
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

        foreach ($memberAssignments as $assignment) {
            if (! in_array($assignment['member_id'], $validMembers)) {
                throw new \Exception('無効なメンバーIDが含まれています。');
            }
        }

        $this->expenseRepository->assignMembers($expense, $memberAssignments);
    }
}
