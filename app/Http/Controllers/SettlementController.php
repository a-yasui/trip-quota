<?php

namespace App\Http\Controllers;

use App\Models\ExpenseSettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TripQuota\Settlement\SettlementService;
use TripQuota\TravelPlan\TravelPlanService;

class SettlementController extends Controller
{
    public function __construct(
        private SettlementService $settlementService,
        private TravelPlanService $travelPlanService
    ) {}

    /**
     * 精算一覧表示
     */
    public function index(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (!$travelPlan) {
            abort(404);
        }

        try {
            $settlements = $this->settlementService->getSettlementsForTravelPlan($travelPlan, Auth::user());
            $statistics = $this->settlementService->getSettlementStatistics($travelPlan, Auth::user());
            
            // 通貨別にグループ化
            $settlementsByCurrency = $settlements->groupBy('currency');
            
        } catch (\Exception $e) {
            abort(403);
        }

        return view('settlements.index', compact(
            'travelPlan', 
            'settlements', 
            'settlementsByCurrency', 
            'statistics'
        ));
    }

    /**
     * 精算計算実行（提案生成）
     */
    public function calculate(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (!$travelPlan) {
            abort(404);
        }

        try {
            $settlementsByCurrency = $this->settlementService->generateSettlementProposal($travelPlan, Auth::user());
            
            $totalSettlements = 0;
            foreach ($settlementsByCurrency as $settlements) {
                $totalSettlements += $settlements->count();
            }

            if ($totalSettlements === 0) {
                return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                    ->with('info', '精算が必要な費用がありません。');
            }

            return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                ->with('success', "精算計算が完了しました。{$totalSettlements}件の精算が提案されています。");
                
        } catch (\Exception $e) {
            return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                ->with('error', '精算計算中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 個別精算の詳細表示
     */
    public function show(string $travelPlanUuid, ExpenseSettlement $settlement)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (!$travelPlan || $settlement->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        try {
            $this->settlementService->getSettlementsForTravelPlan($travelPlan, Auth::user());
        } catch (\Exception $e) {
            abort(403);
        }

        $settlement->load(['payer', 'payee']);

        return view('settlements.show', compact('travelPlan', 'settlement'));
    }

    /**
     * 精算完了の記録
     */
    public function markAsCompleted(string $travelPlanUuid, ExpenseSettlement $settlement)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (!$travelPlan || $settlement->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        try {
            $this->settlementService->markSettlementAsCompleted($settlement, Auth::user());

            return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                ->with('success', '精算を完了として記録しました。');
                
        } catch (\Exception $e) {
            return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                ->with('error', '精算の更新中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 精算情報のリセット（未精算のみ削除）
     */
    public function reset(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (!$travelPlan) {
            abort(404);
        }

        try {
            // 権限チェック
            $this->settlementService->getSettlementsForTravelPlan($travelPlan, Auth::user());
            
            // 未精算の精算情報をクリア
            $this->settlementService->clearByTravelPlan($travelPlan);

            return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                ->with('success', '精算情報をリセットしました。');
                
        } catch (\Exception $e) {
            return redirect()->route('travel-plans.settlements.index', $travelPlan->uuid)
                ->with('error', '精算のリセット中にエラーが発生しました: ' . $e->getMessage());
        }
    }
}