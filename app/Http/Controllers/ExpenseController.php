<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * 経費一覧を表示
     */
    public function index()
    {
        $expenses = Expense::with(['payerMember', 'members', 'travelPlan'])
        ->orderBy('is_settled', 'asc')
        ->orderBy('expense_date', 'desc')
        ->paginate(5);
            
        return view('expenses.index', compact('expenses'));
    }

    /**
     * 経費作成フォームを表示
     */
    public function create(TravelPlan $travelPlan)
    {
        $coreGroup = $travelPlan->groups()->core()->first();
        $branchGroups = $travelPlan->groups()->branch()->with('members')->get();
        $members = $coreGroup->members;
        
        $currencies = Currency::options();
        
        return view('expenses.create', compact('travelPlan', 'branchGroups', 'members', 'currencies'));
    }

    /**
     * 経費を保存
     */
    public function store(ExpenseRequest $request, TravelPlan $travelPlan)
    {
        $validated = $request->validated();
        
        $expense = DB::transaction(function () use ($validated, $request, $travelPlan) {
            $expense = Expense::create([
                'travel_plan_id' => $travelPlan->id,
                'payer_member_id' => $validated['payer_member_id'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'description' => $validated['description'],
                'expense_date' => $validated['expense_date'],
                'category' => $validated['category'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_settled' => false,
            ]);
            
            // 参加メンバーを追加
            $memberCount = count($validated['member_ids']);
            $defaultShareAmount = $validated['amount'] / $memberCount;
            
            // カスタム分配金額があれば使用、なければ均等に分配
            $memberShareAmounts = $request->input('member_share_amounts', []);
            
            // 支払い状態を取得
            $memberPaidStatus = $request->input('member_paid_status', []);
            
            // 全メンバーが支払い済みかどうかを確認するためのフラグ
            $allMembersPaid = true;
            
            foreach ($validated['member_ids'] as $memberId) {
                $shareAmount = isset($memberShareAmounts[$memberId]) && is_numeric($memberShareAmounts[$memberId])
                    ? $memberShareAmounts[$memberId] 
                    : $defaultShareAmount;
                
                // 支払者は常に支払い済み、それ以外はフォームの値を使用
                $isPaid = $memberId == $validated['payer_member_id'] 
                    ? true 
                    : (isset($memberPaidStatus[$memberId]) && $memberPaidStatus[$memberId]);
                
                // 未払いのメンバーがいれば、全員支払い済みフラグをfalseに
                if (!$isPaid) {
                    $allMembersPaid = false;
                }
                
                $expense->members()->attach($memberId, [
                    'share_amount' => $shareAmount,
                    'is_paid' => $isPaid,
                ]);
            }
            
            // 全メンバーが支払い済みの場合、経費全体を精算済みに設定
            if ($allMembersPaid) {
                $expense->update(['is_settled' => true]);
            }
            
            return $expense;
        });
        
        return redirect()->route('expenses.show', $expense)
            ->with('success', '経費を登録しました。');
    }

    /**
     * 経費詳細を表示
     */
    public function show(Expense $expense)
    {
        $expense->load(['payerMember', 'members', 'travelPlan']);
        
        return view('expenses.show', compact('expense'));
    }

    /**
     * 経費編集フォームを表示
     */
    public function edit(Expense $expense)
    {
        $expense->load(['payerMember', 'members', 'travelPlan']);
        
        $travelPlan = $expense->travelPlan;
        $coreGroup = $travelPlan->groups()->core()->first();
        $branchGroups = $travelPlan->groups()->branch()->with('members')->get();
        $members = $coreGroup->members;
        $selectedMemberIds = $expense->members->pluck('id')->toArray();
        
        $currencies = Currency::options();
        
        return view('expenses.edit', compact('expense', 'travelPlan', 'branchGroups', 'members', 'selectedMemberIds', 'currencies'));
    }

    /**
     * 経費を更新
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        $validated = $request->validated();
        
        DB::transaction(function () use ($validated, $request, $expense) {
            $expense->update([
                'payer_member_id' => $validated['payer_member_id'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'description' => $validated['description'],
                'expense_date' => $validated['expense_date'],
                'category' => $validated['category'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // 参加メンバーを更新
            $expense->members()->detach();
            
            $memberCount = count($validated['member_ids']);
            $defaultShareAmount = $validated['amount'] / $memberCount;
            
            // カスタム分配金額があれば使用、なければ均等に分配
            $memberShareAmounts = $request->input('member_share_amounts', []);
            
            // 支払い状態を取得
            $memberPaidStatus = $request->input('member_paid_status', []);
            
            // 全メンバーが支払い済みかどうかを確認するためのフラグ
            $allMembersPaid = true;
            
            foreach ($validated['member_ids'] as $memberId) {
                $shareAmount = isset($memberShareAmounts[$memberId]) && is_numeric($memberShareAmounts[$memberId])
                    ? $memberShareAmounts[$memberId] 
                    : $defaultShareAmount;
                
                // 支払者は常に支払い済み、それ以外はフォームの値を使用
                $isPaid = $memberId == $validated['payer_member_id'] 
                    ? true 
                    : (isset($memberPaidStatus[$memberId]) && $memberPaidStatus[$memberId]);
                
                // 未払いのメンバーがいれば、全員支払い済みフラグをfalseに
                if (!$isPaid) {
                    $allMembersPaid = false;
                }
                
                $expense->members()->attach($memberId, [
                    'share_amount' => $shareAmount,
                    'is_paid' => $isPaid,
                ]);
            }
            
            // 全メンバーが支払い済みの場合、経費全体を精算済みに設定
            if ($allMembersPaid) {
                $expense->update(['is_settled' => true]);
            } else {
                $expense->update(['is_settled' => false]);
            }
        });
        
        return redirect()->route('expenses.show', $expense)
            ->with('success', '経費を更新しました。');
    }

    /**
     * 経費を削除
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        
        return redirect()->route('expenses.index')
            ->with('success', '経費を削除しました。');
    }
    
    /**
     * メンバーの支払い状態を切り替える
     */
    public function togglePaymentStatus(Expense $expense, Member $member)
    {
        // 現在の支払い状態を取得
        $expenseMember = $expense->members()->where('member_id', $member->id)->first()->pivot;
        $currentStatus = $expenseMember->is_paid;
        
        // 支払い状態を反転
        $newStatus = !$currentStatus;
        
        DB::transaction(function () use ($expense, $member, $newStatus) {
            // 支払い状態を更新
            $expense->members()->updateExistingPivot($member->id, [
                'is_paid' => $newStatus,
            ]);
            
            // 全メンバーが支払い済みかどうかを確認
            $allPaid = $expense->members()->wherePivot('is_paid', false)->count() === 0;
            
            // 経費全体の精算状態を更新
            $expense->update([
                'is_settled' => $allPaid,
            ]);
        });
        
        return response()->json([
            'success' => true,
            'is_paid' => $newStatus,
            'is_settled' => $expense->fresh()->is_settled,
        ]);
    }
}
