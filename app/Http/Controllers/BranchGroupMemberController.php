<?php

namespace App\Http\Controllers;

use App\Enums\GroupType;
use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchGroupMemberController extends Controller
{
    /**
     * 班グループにメンバーを追加
     */
    public function store(Request $request, Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }
        
        // バリデーション
        $request->validate([
            'member_id' => [
                'required',
                'exists:members,id',
            ],
        ], [
            'member_id.required' => 'メンバーを選択してください',
            'member_id.exists' => '選択されたメンバーが存在しません',
        ]);
        
        $memberId = $request->member_id;
        $coreMember = Member::find($memberId);
        
        if (!$coreMember) {
            return redirect()->back()
                ->with('error', 'メンバーが見つかりません');
        }
        
        // 同じユーザーが複数のメンバーとして登録されないようにチェック
        if ($coreMember->user_id) {
            $existingMember = Member::where('group_id', $group->id)
                ->where('user_id', $coreMember->user_id)
                ->first();
            
            if ($existingMember) {
                return redirect()->back()
                    ->with('error', 'このユーザーは既に班グループのメンバーです');
            }
        }
        
        // 同じメールアドレスが複数のメンバーとして登録されないようにチェック
        if ($coreMember->email) {
            $existingMember = Member::where('group_id', $group->id)
                ->where('email', $coreMember->email)
                ->first();
            
            if ($existingMember) {
                return redirect()->back()
                    ->with('error', 'このメールアドレスは既に班グループのメンバーとして登録されています');
            }
        }
        
        try {
            return DB::transaction(function () use ($group, $coreMember) {
                // 新しいメンバーを作成
                $newMember = new Member();
                $newMember->name = $coreMember->name;
                $newMember->email = $coreMember->email;
                $newMember->user_id = $coreMember->user_id;
                $newMember->group_id = $group->id;
                $newMember->is_registered = $coreMember->is_registered;
                $newMember->is_active = true;
                $newMember->save();
                
                // 活動ログを記録
                $activityLog = new ActivityLog();
                $activityLog->user_id = Auth::id();
                $activityLog->subject_type = TravelPlan::class;
                $activityLog->subject_id = $group->travel_plan_id;
                $activityLog->action = 'branch_group_member_added';
                $activityLog->description = Auth::user()->name . 'さんが班グループ「' . $group->name . '」に' . $newMember->name . 'を追加しました';
                $activityLog->ip_address = request()->ip();
                $activityLog->save();
                
                return redirect()->route('branch-groups.show', $group)
                    ->with('success', 'メンバーを追加しました');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'メンバー追加に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 班グループからメンバーを削除
     */
    public function destroy(Group $group, Member $member)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }
        
        // メンバーがこのグループに属していることを確認
        if ($member->group_id !== $group->id) {
            return redirect()->route('branch-groups.show', $group)
                ->with('error', 'このメンバーは指定されたグループに属していません');
        }
        
        // 自分自身は削除できない
        if ($member->user_id === Auth::id()) {
            return redirect()->route('branch-groups.show', $group)
                ->with('error', '自分自身をメンバーから削除することはできません');
        }
        
        $memberName = $member->name;
        $travelPlan = $group->travelPlan;
        
        try {
            return DB::transaction(function () use ($group, $member, $memberName, $travelPlan) {
                // メンバーを削除
                $member->delete();
                
                // 活動ログを記録
                $activityLog = new ActivityLog();
                $activityLog->user_id = Auth::id();
                $activityLog->subject_type = TravelPlan::class;
                $activityLog->subject_id = $group->travel_plan_id;
                $activityLog->action = 'branch_group_member_removed';
                $activityLog->description = Auth::user()->name . 'さんが班グループ「' . $group->name . '」から' . $memberName . 'を削除しました';
                $activityLog->ip_address = request()->ip();
                $activityLog->save();
                
                // メンバーが全員削除された場合、グループも削除
                $remainingMembers = $group->members()->count();
                if ($remainingMembers === 0) {
                    $groupName = $group->name;
                    $travelPlanId = $group->travel_plan_id;
                    
                    // グループを削除
                    $group->delete();
                    
                    // 活動ログを記録
                    $activityLog = new ActivityLog();
                    $activityLog->user_id = Auth::id();
                    $activityLog->subject_type = TravelPlan::class;
                    $activityLog->subject_id = $travelPlanId;
                    $activityLog->action = 'branch_group_deleted';
                    $activityLog->description = 'メンバーがいなくなったため、班グループ「' . $groupName . '」が自動的に削除されました';
                    $activityLog->ip_address = request()->ip();
                    $activityLog->save();
                    
                    return redirect()->route('travel-plans.show', $travelPlan)
                        ->with('success', 'メンバーを削除しました。メンバーがいなくなったため、班グループも削除されました。');
                }
                
                return redirect()->route('branch-groups.show', $group)
                    ->with('success', 'メンバーを削除しました');
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'メンバー削除に失敗しました: ' . $e->getMessage());
        }
    }
}
