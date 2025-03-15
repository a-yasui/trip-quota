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
use TripQuota\Group\GroupService;

class BranchGroupMemberController extends Controller
{
    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

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
        $member = Member::find($memberId);

        if (! $member) {
            return redirect()->back()
                ->with('error', 'メンバーが見つかりません');
        }

        try {
            // GroupServiceを使って班グループにメンバーを追加
            $this->groupService->addBranchMember($group, $member);

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $group->travel_plan_id;
            $activityLog->action = 'branch_group_member_added';
            $activityLog->description = Auth::user()->name.'さんが班グループ「'.$group->name.'」に'.$member->name.'を追加しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            return redirect()->route('branch-groups.show', $group)
                ->with('success', 'メンバーを追加しました');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'メンバー追加に失敗しました: '.$e->getMessage());
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

        // メンバーがこのグループに属していることを確認（念のため）
        if (!$group->members()->where('members.id', $member->id)->exists()) {
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
            // GroupServiceを使って班グループからメンバーを削除
            $this->groupService->removeBranchMember($group, $member);

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $group->travel_plan_id;
            $activityLog->action = 'branch_group_member_removed';
            $activityLog->description = Auth::user()->name.'さんが班グループ「'.$group->name.'」から'.$memberName.'を削除しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            // メンバーが全員削除された場合、グループも削除される（GroupServiceのremoveBranchMemberで処理済み）
            // グループが削除されていた場合は旅行計画ページにリダイレクト
            if (!Group::find($group->id)) {
                // 活動ログを記録
                $activityLog = new ActivityLog;
                $activityLog->user_id = Auth::id();
                $activityLog->subject_type = TravelPlan::class;
                $activityLog->subject_id = $travelPlan->id;
                $activityLog->action = 'branch_group_deleted';
                $activityLog->description = 'メンバーがいなくなったため、班グループ「'.$group->name.'」が自動的に削除されました';
                $activityLog->ip_address = request()->ip();
                $activityLog->save();

                return redirect()->route('travel-plans.show', $travelPlan)
                    ->with('success', 'メンバーを削除しました。メンバーがいなくなったため、班グループも削除されました。');
            }

            return redirect()->route('branch-groups.show', $group)
                ->with('success', 'メンバーを削除しました');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'メンバー削除に失敗しました: '.$e->getMessage());
        }
    }
}
