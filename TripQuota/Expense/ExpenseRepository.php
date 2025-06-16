<?php

namespace TripQuota\Expense;

use App\Models\Expense;
use App\Models\Group;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Collection;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function findByTravelPlan(TravelPlan $travelPlan): Collection
    {
        return Expense::where('travel_plan_id', $travelPlan->id)
            ->with(['group', 'paidBy', 'members'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByGroup(Group $group): Collection
    {
        return Expense::where('group_id', $group->id)
            ->with(['group', 'paidBy', 'members'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?Expense
    {
        return Expense::with(['travelPlan', 'group', 'paidBy', 'members'])
            ->find($id);
    }

    public function findByDateRange(TravelPlan $travelPlan, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection
    {
        return Expense::where('travel_plan_id', $travelPlan->id)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with(['group', 'paidBy', 'members'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        // 関連するexpense_member、expense_settlementも自動削除される（外部キー制約）
        return $expense->delete();
    }

    public function assignMembers(Expense $expense, array $memberAssignments): void
    {
        $syncData = [];

        foreach ($memberAssignments as $assignment) {
            $syncData[$assignment['member_id']] = [
                'is_participating' => $assignment['is_participating'] ?? true,
                'amount' => $assignment['amount'] ?? null,
                'is_confirmed' => $assignment['is_confirmed'] ?? false,
            ];
        }

        $expense->members()->sync($syncData);
    }

    public function confirmSplit(Expense $expense): Expense
    {
        $expense->update(['is_split_confirmed' => true]);

        return $expense->fresh();
    }

    public function findUnconfirmedByTravelPlan(TravelPlan $travelPlan): Collection
    {
        return Expense::where('travel_plan_id', $travelPlan->id)
            ->where('is_split_confirmed', false)
            ->with(['group', 'paidBy', 'members'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByMember(int $memberId): Collection
    {
        return Expense::whereHas('members', function ($query) use ($memberId) {
            $query->where('member_id', $memberId);
        })
            ->with(['travelPlan', 'group', 'paidBy', 'members'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
