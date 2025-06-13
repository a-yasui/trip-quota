<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TripQuota\Invitation\InvitationService;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService $invitationService
    ) {}

    /**
     * ユーザーの受信招待一覧表示
     */
    public function index()
    {
        $pendingInvitations = $this->invitationService->getUserPendingInvitations(Auth::user());

        return view('invitations.index', compact('pendingInvitations'));
    }

    /**
     * 招待詳細表示
     */
    public function show(string $token)
    {
        try {
            $invitation = $this->invitationService->findByToken($token);

            if (! $invitation || $invitation->invitee_email !== Auth::user()->email) {
                abort(404);
            }

            return view('invitations.show', compact('invitation'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * 招待受諾処理
     */
    public function accept(string $token)
    {
        try {
            $member = $this->invitationService->acceptInvitation($token, Auth::user());

            return redirect()
                ->route('travel-plans.show', $member->travelPlan->uuid)
                ->with('success', $member->travelPlan->plan_name.'への参加が確定しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 招待拒否処理
     */
    public function decline(string $token)
    {
        try {
            $invitation = $this->invitationService->declineInvitation($token, Auth::user());

            return redirect()
                ->route('invitations.index')
                ->with('success', '招待を拒否しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 招待キャンセル処理（旅行プランメンバー用）
     */
    public function cancel(Request $request, int $invitationId)
    {
        try {
            // TODO: 招待IDから招待を取得する処理
            // この機能は旅行プランの招待管理画面から呼び出される

            return back()
                ->with('success', '招待をキャンセルしました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
