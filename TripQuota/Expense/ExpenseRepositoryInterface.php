<?php

namespace TripQuota\Expense;

use App\Models\Expense;
use App\Models\Group;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryInterface
{
    /**
     * 旅行プランの費用一覧を取得
     */
    public function findByTravelPlan(TravelPlan $travelPlan): Collection;

    /**
     * グループの費用一覧を取得
     */
    public function findByGroup(Group $group): Collection;

    /**
     * IDから費用を取得
     */
    public function findById(int $id): ?Expense;

    /**
     * 日付範囲で費用を取得
     */
    public function findByDateRange(TravelPlan $travelPlan, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection;

    /**
     * 費用を作成
     */
    public function create(array $data): Expense;

    /**
     * 費用を更新
     */
    public function update(Expense $expense, array $data): Expense;

    /**
     * 費用を削除
     */
    public function delete(Expense $expense): bool;

    /**
     * メンバーを費用に割り当て
     */
    public function assignMembers(Expense $expense, array $memberAssignments): void;

    /**
     * 費用の分割計算を確定
     */
    public function confirmSplit(Expense $expense): Expense;

    /**
     * 未確定の費用一覧を取得
     */
    public function findUnconfirmedByTravelPlan(TravelPlan $travelPlan): Collection;

    /**
     * メンバーが参加している費用一覧を取得
     */
    public function findByMember(int $memberId): Collection;
}
