<?php

namespace TripQuota\Settlement;

use App\Models\ExpenseSettlement;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SettlementRepository implements SettlementRepositoryInterface
{
    public function findByTravelPlan(TravelPlan $travelPlan): Collection
    {
        return ExpenseSettlement::where('travel_plan_id', $travelPlan->id)
            ->with(['payer', 'payee'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPendingByTravelPlan(TravelPlan $travelPlan): Collection
    {
        return ExpenseSettlement::where('travel_plan_id', $travelPlan->id)
            ->whereNull('settled_at')
            ->with(['payer', 'payee'])
            ->orderBy('amount', 'desc')
            ->get();
    }

    public function create(array $data): ExpenseSettlement
    {
        return ExpenseSettlement::create($data);
    }

    public function update(ExpenseSettlement $settlement, array $data): ExpenseSettlement
    {
        $settlement->update($data);

        return $settlement->fresh();
    }

    public function markAsSettled(ExpenseSettlement $settlement): ExpenseSettlement
    {
        $settlement->update([
            'settled_at' => now(),
        ]);

        return $settlement->fresh();
    }

    public function delete(ExpenseSettlement $settlement): bool
    {
        return $settlement->delete();
    }

    public function createMultiple(array $settlements): Collection
    {
        return DB::transaction(function () use ($settlements) {
            $created = [];
            foreach ($settlements as $settlementData) {
                $created[] = $this->create($settlementData);
            }

            return new Collection($created);
        });
    }

    public function clearByTravelPlan(TravelPlan $travelPlan): void
    {
        ExpenseSettlement::where('travel_plan_id', $travelPlan->id)
            ->whereNull('settled_at')
            ->delete();
    }
}
