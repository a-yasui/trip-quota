<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use TripQuota\Invitation\InvitationService;
use TripQuota\Member\MemberRepositoryInterface;
use TripQuota\Member\MemberService;

class DashboardController extends Controller
{
    public function __construct(
        private MemberRepositoryInterface $memberRepository,
        private InvitationService $invitationService,
        private MemberService $memberService
    ) {}

    /**
     * ダッシュボード表示
     */
    public function index()
    {
        $user = Auth::user();

        // ユーザーが参加している旅行プラン（確認済みメンバーのみ）
        $confirmedMemberships = $this->memberRepository->findConfirmedByUser($user);
        $travelPlans = $confirmedMemberships->load('travelPlan')->pluck('travelPlan');

        // 未確認の招待数
        $pendingInvitationsCount = $this->invitationService->getUserPendingInvitations($user)->count();

        // 未処理の関連付けリクエスト
        $pendingLinkRequests = $this->memberService->getPendingLinkRequestsForUser($user);

        return view('dashboard', compact('travelPlans', 'pendingInvitationsCount', 'pendingLinkRequests'));
    }
}
