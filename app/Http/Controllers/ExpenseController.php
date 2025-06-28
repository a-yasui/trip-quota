<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TripQuota\Expense\ExpenseService;
use TripQuota\TravelPlan\TravelPlanService;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private TravelPlanService $travelPlanService
    ) {}

    /**
     * 費用一覧表示
     */
    public function index(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan) {
            abort(404);
        }

        try {
            $expenses = $this->expenseService->getExpensesForTravelPlan($travelPlan, Auth::user());
        } catch (\Exception $e) {
            abort(403);
        }

        // グループ別に費用を集計
        $expensesByGroup = $expenses->groupBy('group.name');

        // 総費用を計算
        $totalAmount = $expenses->sum('amount');

        // 通貨別集計
        $amountsByCurrency = $expenses->groupBy('currency')->map(function ($expenses) {
            return $expenses->sum('amount');
        });

        return view('expenses.index', compact('travelPlan', 'expenses', 'expensesByGroup', 'totalAmount', 'amountsByCurrency'));
    }

    /**
     * 費用作成フォーム表示
     */
    public function create(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan) {
            abort(404);
        }

        // 確認済みメンバーとグループを取得
        $members = $travelPlan->members()->where('is_confirmed', true)->get();
        $groups = $travelPlan->groups()->get();

        // デフォルト選択用データを準備
        $coreGroup = $groups->where('group_type', 'CORE')->first();
        $currentUserMember = $members->where('user_id', Auth::id())->first();

        return view('expenses.create', compact(
            'travelPlan',
            'members',
            'groups',
            'coreGroup',
            'currentUserMember'
        ));
    }

    /**
     * 費用作成処理
     */
    public function store(Request $request, string $travelPlanUuid)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:3',
            'expense_date' => 'required|date',
            'group_id' => 'required|integer|exists:groups,id',
            'paid_by_member_id' => 'required|integer|exists:members,id',
            'member_assignments' => 'nullable|array',
            'member_assignments.*.member_id' => 'required|integer|exists:members,id',
            'member_assignments.*.is_participating' => 'boolean',
            'member_assignments.*.amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan) {
                abort(404);
            }

            $this->expenseService->createExpense($travelPlan, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.expenses.create', $travelPlan->uuid)
                ->with('success', '費用を追加しました。次の費用を追加できます。');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 費用詳細表示
     */
    public function show(string $travelPlanUuid, Expense $expense)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan || $expense->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // 現在のユーザーを自動参加させる
        $this->expenseService->autoParticipateCurrentUser($expense, Auth::user());

        // 分割計算結果を取得
        $splitAmounts = $this->expenseService->calculateSplitAmounts($expense);

        // 現在のユーザーのメンバー情報を取得
        $currentUserMember = $travelPlan->members()
            ->where('user_id', Auth::id())
            ->where('is_confirmed', true)
            ->first();

        return view('expenses.show', compact('travelPlan', 'expense', 'splitAmounts', 'currentUserMember'));
    }

    /**
     * 費用編集フォーム表示
     */
    public function edit(string $travelPlanUuid, Expense $expense)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan || $expense->travel_plan_id !== $travelPlan->id) {
            abort(404);
        }

        // 確認済みメンバーとグループを取得
        $members = $travelPlan->members()->where('is_confirmed', true)->get();
        $groups = $travelPlan->groups()->get();

        // 現在の費用メンバー割り当てを取得
        $expense->load(['members']);

        return view('expenses.edit', compact('travelPlan', 'expense', 'members', 'groups'));
    }

    /**
     * 費用更新処理
     */
    public function update(Request $request, string $travelPlanUuid, Expense $expense)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:3',
            'expense_date' => 'required|date',
            'group_id' => 'required|integer|exists:groups,id',
            'paid_by_member_id' => 'required|integer|exists:members,id',
            'member_assignments' => 'nullable|array',
            'member_assignments.*.member_id' => 'required|integer|exists:members,id',
            'member_assignments.*.is_participating' => 'boolean',
            'member_assignments.*.amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan || $expense->travel_plan_id !== $travelPlan->id) {
                abort(404);
            }

            $this->expenseService->updateExpense($expense, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.expenses.show', [$travelPlan->uuid, $expense->id])
                ->with('success', '費用を更新しました。');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 費用削除処理
     */
    public function destroy(string $travelPlanUuid, Expense $expense)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan || $expense->travel_plan_id !== $travelPlan->id) {
                abort(404);
            }

            $this->expenseService->deleteExpense($expense, Auth::user());

            return redirect()
                ->route('travel-plans.expenses.index', $travelPlan->uuid)
                ->with('success', '費用を削除しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 分割金額を更新
     */
    public function updateSplits(Request $request, string $travelPlanUuid, Expense $expense)
    {
        $validated = $request->validate([
            'splits' => 'required|array',
            'splits.*.member_id' => 'required|integer|exists:members,id',
            'splits.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan || $expense->travel_plan_id !== $travelPlan->id) {
                abort(404);
            }

            $this->expenseService->updateExpenseSplits($expense, Auth::user(), $validated['splits']);

            return redirect()
                ->route('travel-plans.expenses.show', [$travelPlan->uuid, $expense->id])
                ->with('success', '分割金額を更新しました。');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
