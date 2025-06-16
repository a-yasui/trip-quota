<?php

namespace TripQuota\Settlement;

use App\Models\ExpenseSettlement;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Collection;

interface SettlementRepositoryInterface
{
    /**
     * 旅行プランの精算情報を取得
     */
    public function findByTravelPlan(TravelPlan $travelPlan): Collection;

    /**
     * 未精算の精算情報を取得
     */
    public function findPendingByTravelPlan(TravelPlan $travelPlan): Collection;

    /**
     * 精算情報を作成
     */
    public function create(array $data): ExpenseSettlement;

    /**
     * 精算情報を更新
     */
    public function update(ExpenseSettlement $settlement, array $data): ExpenseSettlement;

    /**
     * 精算を完了としてマーク
     */
    public function markAsSettled(ExpenseSettlement $settlement): ExpenseSettlement;

    /**
     * 精算情報を削除
     */
    public function delete(ExpenseSettlement $settlement): bool;

    /**
     * 複数の精算情報を一括作成
     */
    public function createMultiple(array $settlements): Collection;

    /**
     * 既存の精算情報をクリア
     */
    public function clearByTravelPlan(TravelPlan $travelPlan): void;
}