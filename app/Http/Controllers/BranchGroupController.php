<?php

namespace App\Http\Controllers;

use App\Enums\GroupType;
use App\Http\Requests\BranchGroupRequest;
use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class BranchGroupController extends Controller
{
    /**
     * 班グループ作成フォームを表示
     */
    public function create(TravelPlan $travelPlan)
    {
        // コアグループを取得
        $coreGroup = $travelPlan->groups()->where('type', GroupType::CORE)->first();

        if (! $coreGroup) {
            return redirect()->route('travel-plans.show', $travelPlan)
                ->with('error', 'コアグループが見つかりません');
        }

        // コアグループのメンバーを取得
        $members = $coreGroup->members()->active()->get();

        return view('branch-groups.create', compact('travelPlan', 'coreGroup', 'members'));
    }

    /**
     * 班グループを保存
     */
    public function store(BranchGroupRequest $request, TravelPlan $travelPlan)
    {
        try {
            DB::beginTransaction();

            // コアグループを取得
            $coreGroup = $travelPlan->groups()->where('type', GroupType::CORE)->first();

            if (! $coreGroup) {
                return redirect()->route('travel-plans.show', $travelPlan)
                    ->with('error', 'コアグループが見つかりません');
            }

            // 班グループを作成
            $branchGroup = new Group;
            $branchGroup->name = $request->name;
            $branchGroup->type = GroupType::BRANCH;
            $branchGroup->travel_plan_id = $travelPlan->id;
            $branchGroup->parent_group_id = $coreGroup->id;
            $branchGroup->save();

            // 選択されたメンバーを班グループに追加
            $memberIds = $request->members;
            foreach ($memberIds as $memberId) {
                $member = Member::find($memberId);

                // 同じユーザーが複数のメンバーとして登録されないようにチェック
                if ($member && $member->user_id) {
                    $existingMember = Member::where('group_id', $branchGroup->id)
                        ->where('user_id', $member->user_id)
                        ->first();

                    if ($existingMember) {
                        continue; // 既に登録されている場合はスキップ
                    }
                }

                // 新しいメンバーを作成
                $newMember = new Member;
                $newMember->name = $member->name;
                $newMember->email = $member->email;
                $newMember->user_id = $member->user_id;
                $newMember->group_id = $branchGroup->id;
                $newMember->is_active = true;
                $newMember->save();
            }

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $travelPlan->id;
            $activityLog->action = 'branch_group_created';
            $activityLog->description = Auth::user()->name.'さんが班グループ「'.$branchGroup->name.'」を作成しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            DB::commit();

            return redirect()->route('travel-plans.show', $travelPlan)
                ->with('success', '班グループを作成しました');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', '班グループの作成に失敗しました: '.$e->getMessage());
        }
    }

    /**
     * 班グループの詳細を表示
     */
    public function show(Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }

        // メンバーを取得
        $members = $group->members()->active()->get();

        // コアグループを取得
        $coreGroup = $group->parentGroup;

        // コアグループのメンバーを取得（まだ班グループに追加されていないメンバー）
        $availableMembers = collect();
        if ($coreGroup) {
            $branchMemberUserIds = $members->pluck('user_id')->filter()->all();
            $branchMemberEmails = $members->pluck('email')->filter()->all();

            $availableMembers = $coreGroup->members()
                ->active()
                ->where(function ($query) use ($branchMemberUserIds) {
                    $query->whereNull('user_id')
                        ->orWhereNotIn('user_id', $branchMemberUserIds);
                })
                ->where(function ($query) use ($branchMemberEmails) {
                    $query->whereNull('email')
                        ->orWhereNotIn('email', $branchMemberEmails);
                })
                ->get();
        }

        return view('branch-groups.show', compact('group', 'members', 'availableMembers'));
    }

    /**
     * 班グループ編集フォームを表示
     */
    public function edit(Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }

        // メンバーを取得
        $members = $group->members()->active()->get();

        return view('branch-groups.edit', compact('group', 'members'));
    }

    /**
     * 班グループを更新
     */
    public function update(Request $request, Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }

        // バリデーション
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('groups', 'name')
                    ->where(function ($query) use ($group) {
                        return $query->where('travel_plan_id', $group->travel_plan_id)
                            ->where('type', GroupType::BRANCH);
                    })
                    ->ignore($group->id),
            ],
        ], [
            'name.required' => '班グループ名は必須です',
            'name.unique' => 'この班グループ名は既に使用されています',
        ]);

        try {
            DB::beginTransaction();

            // 班グループを更新
            $group->name = $request->name;
            $group->description = $request->description;
            $group->save();

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $group->travel_plan_id;
            $activityLog->action = 'branch_group_updated';
            $activityLog->description = Auth::user()->name.'さんが班グループ「'.$group->name.'」を更新しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            DB::commit();

            return redirect()->route('branch-groups.show', $group)
                ->with('success', '班グループを更新しました');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', '班グループの更新に失敗しました: '.$e->getMessage());
        }
    }

    /**
     * 班グループを削除
     */
    public function destroy(Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }

        try {
            DB::beginTransaction();

            $travelPlan = $group->travelPlan;
            $groupName = $group->name;

            // メンバーを削除
            foreach ($group->members as $member) {
                $member->delete();
            }

            // グループを削除
            $group->delete();

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $travelPlan->id;
            $activityLog->action = 'branch_group_deleted';
            $activityLog->description = Auth::user()->name.'さんが班グループ「'.$groupName.'」を削除しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            DB::commit();

            return redirect()->route('travel-plans.show', $travelPlan)
                ->with('success', '班グループを削除しました');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', '班グループの削除に失敗しました: '.$e->getMessage());
        }
    }

    /**
     * 班グループ複製フォームを表示
     */
    public function duplicate(Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }

        // メンバーを取得
        $members = $group->members()->active()->get();

        return view('branch-groups.duplicate', compact('group', 'members'));
    }

    /**
     * 班グループの複製を保存
     */
    public function storeDuplicate(Request $request, Group $group)
    {
        // 班グループであることを確認
        if ($group->type !== GroupType::BRANCH) {
            return redirect()->route('travel-plans.show', $group->travelPlan)
                ->with('error', '指定されたグループは班グループではありません');
        }

        // バリデーション
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->where(function ($query) use ($group) {
                    return $query->where('travel_plan_id', $group->travel_plan_id)
                        ->where('type', GroupType::BRANCH);
                }),
            ],
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => '班グループ名は必須です',
            'name.unique' => 'この班グループ名は既に使用されています',
        ]);

        try {
            DB::beginTransaction();

            // 新しい班グループを作成
            $newGroup = new Group;
            $newGroup->name = $request->name;
            $newGroup->description = $request->description;
            $newGroup->type = GroupType::BRANCH;
            $newGroup->travel_plan_id = $group->travel_plan_id;
            $newGroup->parent_group_id = $group->parent_group_id;
            $newGroup->save();

            // 元の班グループのメンバーを複製
            $memberIds = $group->members()->pluck('members.id');

            $newGroup->branchMembers()->attach($memberIds);

            // デバッグ出力を追加
            Log::info('新しいグループが作成されました', ['group_id' => $newGroup->id, 'name' => $newGroup->name]);

            // 活動ログを記録
            $activityLog = new ActivityLog;
            $activityLog->user_id = Auth::id();
            $activityLog->subject_type = TravelPlan::class;
            $activityLog->subject_id = $group->travel_plan_id;
            $activityLog->action = 'branch_group_duplicated';
            $activityLog->description = Auth::user()->name.'さんが班グループ「'.$group->name.'」から「'.$newGroup->name.'」を複製しました';
            $activityLog->ip_address = request()->ip();
            $activityLog->save();

            DB::commit();

            return redirect()->route('branch-groups.show', $newGroup)
                ->with('success', '班グループを複製しました');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', '班グループの複製に失敗しました: '.$e->getMessage());
        }
    }
}
