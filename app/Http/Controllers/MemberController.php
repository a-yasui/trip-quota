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
     * メンバー追加処理（新仕様）
     */
    public function store(Request $request, string $travelPlanUuid)
    {
        $validated = $request->validate([
            'member_type' => 'required|in:name_only,with_invitation',
            'name' => 'required|string|max:255',
            'invitation_type' => 'required_if:member_type,with_invitation|nullable|in:email,account',
            'email' => [
                'nullable',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->member_type === 'with_invitation' &&
                        $request->invitation_type === 'email' &&
                        empty($value)) {
                        $fail('メールアドレスは必須です。');
                    }
                },
            ],
            'account_name' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->member_type === 'with_invitation' &&
                        $request->invitation_type === 'account' &&
                        empty($value)) {
                        $fail('アカウント名は必須です。');
                    }
                },
            ],
        ]);

        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan) {
                abort(404);
            }

            // Policyで招待権限をチェック
            $this->authorize('invite', [Member::class, $travelPlan]);

            if ($validated['member_type'] === 'name_only') {
                // 表示名のみでメンバー登録
                $member = $this->memberService->createMemberByNameOnly(
                    $travelPlan,
                    Auth::user(),
                    $validated['name']
                );
                $message = 'メンバー「'.$validated['name'].'」を追加しました。';
            } else {
                // 招待付きメンバー登録
                if ($validated['invitation_type'] === 'email') {
                    if (empty($validated['email'])) {
                        throw ValidationException::withMessages([
                            'email' => ['メールアドレスは必須です。'],
                        ]);
                    }
                    $this->memberService->inviteMemberByEmail(
                        $travelPlan,
                        Auth::user(),
                        $validated['email'],
                        $validated['name']
                    );
                    $message = $validated['email'].'に招待を送信しました。';
                } else {
                    // アカウント名招待の場合
                    if (empty($validated['account_name']) || ! is_string($validated['account_name'])) {
                        throw ValidationException::withMessages([
                            'account_name' => ['アカウント名は必須です。'],
                        ]);
                    }

                    $this->memberService->inviteMemberByAccountName(
                        $travelPlan,
                        Auth::user(),
                        $validated['account_name']
                    );
                    $message = '@'.$validated['account_name'].'に招待を送信しました。';
                }
            }

            return redirect()
                ->route('travel-plans.members.index', $travelPlan->uuid)
                ->with('success', $message);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            // ログに詳細なエラーを記録
            \Log::error('Member creation failed', [
                'user_id' => Auth::id(),
                'travel_plan_uuid' => $travelPlanUuid ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()
                ->withInput()
                ->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
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

            // 既存のメールアドレスがある場合のみメールアドレスを必須にする
            $rules = [
                'name' => 'required|string|max:255',
                'groups' => 'nullable|array',
                'groups.*' => 'integer|exists:groups,id',
            ];

            if ($member->email) {
                $rules['email'] = 'required|email|max:255';
            } else {
                $rules['email'] = 'nullable|email|max:255';
            }

            $validated = $request->validate($rules);

            $updatedMember = $this->memberService->updateMember($member, Auth::user(), $validated);

            // グループの所属を更新
            if (array_key_exists('groups', $validated)) {
                $selectedGroups = $validated['groups'] ?? [];
                // 旅行プランに属するグループのみを許可
                $validGroups = $travelPlan->groups()->whereIn('id', $selectedGroups)->pluck('id')->toArray();
                $updatedMember->groups()->sync($validGroups);
            }

            return redirect()
                ->route('travel-plans.members.show', [$travelPlan->uuid, $updatedMember->id])
                ->with('success', 'メンバー情報を更新しました。');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            // ログに詳細なエラーを記録
            \Log::error('Member update failed', [
                'user_id' => Auth::id(),
                'travel_plan_uuid' => $travelPlanUuid ?? null,
                'member_id' => $memberId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()
                ->withInput()
                ->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
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
            // ログに詳細なエラーを記録
            \Log::error('Member deletion failed', [
                'user_id' => Auth::id(),
                'travel_plan_uuid' => $travelPlanUuid ?? null,
                'member_id' => $memberId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()
                ->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
        }
    }

    /**
     * メンバー関連付けリクエスト送信
     */
    public function sendLinkRequest(Request $request, string $travelPlanUuid, string $memberId)
    {
        $validated = $request->validate([
            'link_type' => 'required|in:email,account',
            'email' => 'required_if:link_type,email|nullable|email|max:255',
            'account_name' => 'required_if:link_type,account|nullable|string|max:255',
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

            if ($validated['link_type'] === 'email') {
                $linkRequest = $this->memberService->createLinkRequest(
                    $member,
                    Auth::user(),
                    $validated['email']
                );
                $message = $validated['email'].'に関連付けリクエストを送信しました。';
            } else {
                $linkRequest = $this->memberService->createLinkRequest(
                    $member,
                    Auth::user(),
                    null,
                    $validated['account_name']
                );
                $message = '@'.$validated['account_name'].'に関連付けリクエストを送信しました。';
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            // ログに詳細なエラーを記録
            \Log::error('Member link request creation failed', [
                'user_id' => Auth::id(),
                'travel_plan_uuid' => $travelPlanUuid ?? null,
                'member_id' => $memberId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()
                ->withInput()
                ->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
        }
    }

    /**
     * メンバーを確認済みにする（旅行プラン作成者のみ）
     */
    public function confirmMember(string $travelPlanUuid, Member $member)
    {
        try {
            $travelPlan = $this->travelPlanService->getTravelPlanByUuid($travelPlanUuid);

            if (! $travelPlan || $member->travel_plan_id !== $travelPlan->id) {
                abort(404);
            }

            // メンバーにtravelPlanリレーションをロード
            $member->load('travelPlan');

            $this->memberService->confirmMember($member, Auth::user());

            return back()
                ->with('success', 'メンバー「'.$member->name.'」を確認済みにしました。');
        } catch (\Exception $e) {
            // ログに詳細なエラーを記録
            \Log::error('Member confirmation failed', [
                'user_id' => Auth::id(),
                'travel_plan_uuid' => $travelPlanUuid ?? null,
                'member_id' => $member->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()
                ->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
        }
    }

    /**
     * メンバー関連付けリクエストを承認
     */
    public function approveLinkRequest(\App\Models\MemberLinkRequest $linkRequest, Request $request)
    {
        try {
            // 1. 基本認証チェック
            if ($linkRequest->target_user_id !== Auth::id()) {
                abort(403, 'このリクエストを承認する権限がありません。');
            }

            // 2. 追加のconfirmationトークン検証
            $request->validate([
                'confirmation' => 'required|string',
            ]);

            if ($request->confirmation !== 'approve-' . $linkRequest->id) {
                abort(422, '無効な確認トークンです。');
            }

            // 3. Rate limiting
            $rateLimitKey = 'approve-link:' . Auth::id();
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                abort(429, '操作回数が多すぎます。しばらくお待ちください。');
            }

            if (!$linkRequest->isPending()) {
                return back()->withErrors(['error' => 'このリクエストはすでに処理されているか、期限が切れています。']);
            }

            $this->memberService->approveLinkRequest($linkRequest, Auth::user());

            // Rate limitingヒット
            \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 300); // 5分間制限

            // セッション再生成
            $request->session()->regenerate();

            return redirect()
                ->route('dashboard')
                ->with('success', 'メンバー関連付けリクエストを承認しました。');
        } catch (\Exception $e) {
            // ログに詳細なエラーを記録
            \Log::error('Member link request approval failed', [
                'user_id' => Auth::id(),
                'link_request_id' => $linkRequest->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
        }
    }

    /**
     * メンバー関連付けリクエストを拒否
     */
    public function declineLinkRequest(\App\Models\MemberLinkRequest $linkRequest, Request $request)
    {
        try {
            // 1. 基本認証チェック
            if ($linkRequest->target_user_id !== Auth::id()) {
                abort(403, 'このリクエストを拒否する権限がありません。');
            }

            // 2. 追加のconfirmationトークン検証
            $request->validate([
                'confirmation' => 'required|string',
            ]);

            if ($request->confirmation !== 'decline-' . $linkRequest->id) {
                abort(422, '無効な確認トークンです。');
            }

            // 3. Rate limiting
            $rateLimitKey = 'decline-link:' . Auth::id();
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                abort(429, '操作回数が多すぎます。しばらくお待ちください。');
            }

            if (!$linkRequest->isPending()) {
                return back()->withErrors(['error' => 'このリクエストはすでに処理されているか、期限が切れています。']);
            }

            $this->memberService->declineLinkRequest($linkRequest, Auth::user());

            // Rate limitingヒット
            \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 300); // 5分間制限

            // セッション再生成
            $request->session()->regenerate();

            return redirect()
                ->route('dashboard')
                ->with('success', 'メンバー関連付けリクエストを拒否しました。');
        } catch (\Exception $e) {
            // ログに詳細なエラーを記録
            \Log::error('Member link request decline failed', [
                'user_id' => Auth::id(),
                'link_request_id' => $linkRequest->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // ユーザーには汎用的なメッセージを表示
            return back()->withErrors(['error' => '処理中にエラーが発生しました。しばらくしてからもう一度お試しください。']);
        }
    }
}
