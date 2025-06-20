<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use TripQuota\Group\GroupService;
use TripQuota\Member\MemberService;
use TripQuota\TravelPlan\TravelPlanService;

class GroupController extends Controller
{
    public function __construct(
        private GroupService $groupService,
        private MemberService $memberService,
        private TravelPlanService $travelPlanService
    ) {}

    /**
     * 旅行プランのグループ一覧表示
     */
    public function index(string $travelPlanUuid)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $coreGroup = $this->groupService->getCoreGroup($travelPlan, Auth::user());
            $branchGroups = $this->groupService->getBranchGroups($travelPlan, Auth::user());

            return view('groups.index', compact('travelPlan', 'groups', 'coreGroup', 'branchGroups'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * 班グループ作成フォーム表示
     */
    public function create(string $travelPlanUuid)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            return view('groups.create', compact('travelPlan'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * 班グループ作成処理
     */
    public function store(Request $request, string $travelPlanUuid)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $group = $this->groupService->createBranchGroup($travelPlan, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.groups.index', $travelPlan->uuid)
                ->with('success', '班グループを作成しました。');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * グループ詳細表示
     */
    public function show(string $travelPlanUuid, int $groupId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group) {
                abort(404);
            }

            // グループメンバーと旅行プランの全メンバーを取得
            $groupMembers = $this->groupService->getGroupMembers($group, Auth::user());
            $allMembers = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
            
            // 現在のユーザーのメンバー情報と参加状態を取得
            $currentMember = $this->memberService->findMemberByTravelPlanAndUser($travelPlan, Auth::user());
            $isCurrentUserInGroup = $currentMember ? $groupMembers->contains('id', $currentMember->id) : false;

            return view('groups.show', compact('travelPlan', 'group', 'groupMembers', 'allMembers', 'currentMember', 'isCurrentUserInGroup'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * グループ編集フォーム表示
     */
    public function edit(string $travelPlanUuid, int $groupId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group || $group->type === 'CORE') {
                abort(404);
            }

            return view('groups.edit', compact('travelPlan', 'group'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * グループ更新処理
     */
    public function update(Request $request, string $travelPlanUuid, int $groupId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group) {
                abort(404);
            }

            $updatedGroup = $this->groupService->updateGroup($group, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.groups.show', [$travelPlan->uuid, $updatedGroup->id])
                ->with('success', 'グループを更新しました。');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * グループ削除処理
     */
    public function destroy(string $travelPlanUuid, int $groupId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group) {
                abort(404);
            }

            $this->groupService->deleteGroup($group, Auth::user());

            return redirect()
                ->route('travel-plans.groups.index', $travelPlan->uuid)
                ->with('success', 'グループを削除しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * グループにメンバーを追加
     */
    public function addMember(Request $request, string $travelPlanUuid, int $groupId)
    {
        $validated = $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group) {
                abort(404);
            }

            $members = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
            $member = $members->find($validated['member_id']);

            if (! $member) {
                abort(404);
            }

            $this->groupService->addMemberToGroup($group, $member, Auth::user());

            return redirect()
                ->route('travel-plans.groups.show', [$travelPlan->uuid, $group->id])
                ->with('success', 'メンバーをグループに追加しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * グループからメンバーを削除
     */
    public function removeMember(Request $request, string $travelPlanUuid, int $groupId, int $memberId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group) {
                abort(404);
            }

            $groupMembers = $this->groupService->getGroupMembers($group, Auth::user());
            $member = $groupMembers->find($memberId);

            if (! $member) {
                abort(404);
            }

            $this->groupService->removeMemberFromGroup($group, $member, Auth::user());

            return redirect()
                ->route('travel-plans.groups.show', [$travelPlan->uuid, $group->id])
                ->with('success', 'メンバーをグループから削除しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * グループに自分自身で参加する
     */
    public function join(string $travelPlanUuid, int $groupId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $groups = $this->groupService->getGroupsForTravelPlan($travelPlan, Auth::user());
            $group = $groups->find($groupId);

            if (! $group) {
                abort(404);
            }

            // 現在のユーザーのメンバー情報を取得
            $currentMember = $this->memberService->findMemberByTravelPlanAndUser($travelPlan, Auth::user());

            if (! $currentMember) {
                abort(403, 'この旅行プランのメンバーではありません。');
            }

            // 既にグループに参加しているかチェック
            $groupMembers = $this->groupService->getGroupMembers($group, Auth::user());
            $isAlreadyMember = $groupMembers->contains('id', $currentMember->id);

            if ($isAlreadyMember) {
                return redirect()
                    ->route('travel-plans.groups.show', [$travelPlan->uuid, $group->id])
                    ->with('info', '既にこのグループに参加しています。');
            }

            $this->groupService->addMemberToGroup($group, $currentMember, Auth::user());

            return redirect()
                ->route('travel-plans.groups.show', [$travelPlan->uuid, $group->id])
                ->with('success', 'グループに参加しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
