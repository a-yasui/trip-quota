<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupMemberStoreRequest;
use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TripQuota\Group\GroupService;
use TripQuota\User\UserService;

class GroupMemberController extends Controller
{
    protected GroupService $groupService;
    protected UserService $userService;

    public function __construct(GroupService $groupService, UserService $userService)
    {
        $this->groupService = $groupService;
        $this->userService = $userService;
    }

    /**
     * メンバーを追加するフォームを表示
     */
    public function create(Group $group)
    {
        return view('group-members.create', compact('group'));
    }

    /**
     * 新しいメンバーを保存
     */
    public function store(GroupMemberStoreRequest $request, Group $group)
    {
        try {
            // トランザクションが既に開始されていない場合のみ開始
            if (DB::transactionLevel() === 0) {
                DB::beginTransaction();
            }

            // 旅行計画を取得
            $travelPlan = $group->travelPlan;

            // メールアドレスが入力され、そのアドレスが登録ユーザーのものである場合
            if ($request->email) {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    // ユーザーが既にこのグループのメンバーとして登録されているかチェック
                    $coreGroup = $travelPlan->groups()->where('type', \App\Enums\GroupType::CORE)->first();
                    if ($coreGroup) {
                        $existingMember = Member::where('group_id', $coreGroup->id)
                            ->where('user_id', $user->id)
                            ->first();

                        if ($existingMember) {
                            return redirect()->route('groups.members.create', $group)
                                ->withInput()
                                ->with('error', 'このユーザーは既にメンバーとして登録されています');
                        }
                    }
                }
            }

            // UserService を使用してメンバー作成
            $memberName = $request->name ?? $request->email;
            $member = $this->userService->createMember($travelPlan, $memberName, $request->email);

            // コアグループとは別に、元のリクエストで指定された班グループ（$group）にもメンバーを関連付ける
            if ($group->type === \App\Enums\GroupType::BRANCH) {
                $this->groupService->addBranchMember($group, $member);
            }

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $group->travel_plan_id;
            $activityLog->action = 'member_added';
            $activityLog->description = Auth::user()->name.'さんが'.$member->name.'をメンバーに追加しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            // トランザクションが開始されている場合のみコミット
            if (DB::transactionLevel() > 0) {
                DB::commit();
            }

            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('success', 'メンバーを追加しました');

        } catch (\Exception $e) {
            // トランザクションが開始されている場合のみロールバック
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'メンバー追加に失敗しました: '.$e->getMessage());
        }
    }

    /**
     * メンバーを削除
     */
    public function destroy(Group $group, Member $member)
    {
        try {
            // 権限チェック
            $user = Auth::user();
            $travelPlan = $group->travelPlan;

            if ($user->id !== $travelPlan->creator_id && $user->id !== $travelPlan->deletion_permission_holder_id) {
                abort(403, 'メンバーを削除する権限がありません。');
            }

            // 自分自身は削除できない
            if ($user->id === $member->user_id) {
                return redirect()->back()
                    ->with('error', '自分自身をメンバーから削除することはできません。');
            }

            // UserService を使ってメンバーを削除
            $this->userService->removeMember($travelPlan, $member);

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $group->travel_plan_id;
            $activityLog->action = 'member_removed';
            $activityLog->description = Auth::user()->name.'さんが'.$member->name.'をメンバーから削除しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            return redirect()->route('travel-plans.show', $travelPlan)
                ->with('success', 'メンバーを削除しました');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'メンバー削除に失敗しました: '.$e->getMessage());
        }
    }
}
