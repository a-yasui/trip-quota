<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use TripQuota\Invitation\InvitationService;
use TripQuota\Member\MemberService;
use TripQuota\TravelPlan\TravelPlanService;

class MemberController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private MemberService $memberService,
        private TravelPlanService $travelPlanService,
        private InvitationService $invitationService
    ) {}

    /**
     * 旅行プランのメンバー一覧表示
     */
    public function index(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan) {
            abort(404);
        }

        // Policyで認可チェック
        $this->authorize('viewAnyForTravelPlan', [Member::class, $travelPlan]);

        $members = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
        $confirmedMembers = $this->memberService->getConfirmedMembers($travelPlan, Auth::user());
        $unconfirmedMembers = $this->memberService->getUnconfirmedMembers($travelPlan, Auth::user());
        $pendingInvitations = $this->invitationService->getPendingInvitationsForTravelPlan($travelPlan, Auth::user());

        return view('members.index', compact('travelPlan', 'members', 'confirmedMembers', 'unconfirmedMembers', 'pendingInvitations'));
    }

    /**
     * メンバー招待フォーム表示
     */
    public function create(string $travelPlanUuid)
    {
        $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

        if (! $travelPlan) {
            abort(404);
        }

        // Policyで招待権限をチェック
        $this->authorize('invite', [Member::class, $travelPlan]);

        return view('members.create', compact('travelPlan'));
    }

    /**
     * メンバー招待処理
     */
    public function store(Request $request, string $travelPlanUuid)
    {
        $validated = $request->validate([
            'invitation_type' => 'required|in:email,account',
            'email' => 'required_if:invitation_type,email|nullable|email|max:255',
            'account_name' => 'required_if:invitation_type,account|nullable|string|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan) {
                abort(404);
            }

            // Policyで招待権限をチェック
            $this->authorize('invite', [Member::class, $travelPlan]);

            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            if ($validated['invitation_type'] === 'email') {
                $this->memberService->inviteMemberByEmail(
                    $travelPlan,
                    Auth::user(),
                    $validated['email'],
                    $validated['name'] ?? null
                );
                $message = $validated['email'].'に招待を送信しました。';
            } else {
                // アカウント名招待の場合、account_nameが必須
                if (empty($validated['account_name']) || ! is_string($validated['account_name'])) {
                    throw new \Exception('アカウント名が正しく入力されていません。');
                }

                $this->memberService->inviteMemberByAccountName(
                    $travelPlan,
                    Auth::user(),
                    $validated['account_name']
                );
                $message = '@'.$validated['account_name'].'に招待を送信しました。';
            }

            return redirect()
                ->route('travel-plans.members.index', $travelPlan->uuid)
                ->with('success', $message);
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
     * メンバー詳細表示
     */
    public function show(string $travelPlanUuid, string $memberId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $members = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
            $member = $members->find((int) $memberId);

            if (! $member) {
                abort(404);
            }

            return view('members.show', compact('travelPlan', 'member'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * メンバー編集フォーム表示
     */
    public function edit(string $travelPlanUuid, string $memberId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $members = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
            $member = $members->find((int) $memberId);

            if (! $member) {
                abort(404);
            }

            // グループ一覧を取得
            $availableGroups = $travelPlan->groups()->orderBy('type')->orderBy('name')->get();

            return view('members.edit', compact('travelPlan', 'member', 'availableGroups'));
        } catch (\Exception $e) {
            abort(403);
        }
    }

    /**
     * メンバー更新処理
     */
    public function update(Request $request, string $travelPlanUuid, string $memberId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $members = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
            $member = $members->find((int) $memberId);

            if (! $member) {
                abort(404);
            }

            $updatedMember = $this->memberService->updateMember($member, Auth::user(), $validated);

            return redirect()
                ->route('travel-plans.members.show', [$travelPlan->uuid, $updatedMember->id])
                ->with('success', 'メンバー情報を更新しました。');
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
     * メンバー削除処理
     */
    public function destroy(string $travelPlanUuid, string $memberId)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlan($travelPlanUuid, Auth::user());

            if (! $travelPlan) {
                abort(404);
            }

            $members = $this->memberService->getMembersForTravelPlan($travelPlan, Auth::user());
            $member = $members->find((int) $memberId);

            if (! $member) {
                abort(404);
            }

            $this->memberService->removeMember($member, Auth::user());

            return redirect()
                ->route('travel-plans.members.index', $travelPlan->uuid)
                ->with('success', 'メンバーを削除しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
