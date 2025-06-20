@extends('layouts.master')

@section('title', 'メンバー管理 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container')
        @component('components.page-header', ['title' => 'メンバー管理', 'subtitle' => $travelPlan->plan_name])
            @slot('action')
                <a href="{{ route('travel-plans.members.create', $travelPlan->uuid) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    メンバーを招待
                </a>
            @endslot
        @endcomponent

        @include('components.alerts')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 確認済みメンバー -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">確認済みメンバー ({{ $confirmedMembers->count() }})</h2>
                </div>
                <div class="px-6 py-4">
                    @if($confirmedMembers->count() > 0)
                        <div class="space-y-4">
                            @foreach($confirmedMembers as $member)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-green-700">
                                                {{ substr($member->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                            @if($travelPlan->owner_user_id === $member->user_id)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    所有者
                                                </span>
                                            @endif
                                            @if($travelPlan->creator_user_id === $member->user_id)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-1">
                                                    作成者
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('travel-plans.members.show', [$travelPlan->uuid, $member->id]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細
                                        </a>
                                        @if($member->user_id !== $travelPlan->owner_user_id)
                                            <form method="POST" action="{{ route('travel-plans.members.destroy', [$travelPlan->uuid, $member->id]) }}" 
                                                  onsubmit="return confirm('本当に削除しますか？')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    削除
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">確認済みメンバーがいません</p>
                    @endif
                </div>
            </div>

            <!-- 未確認メンバー・招待中 -->
            <div class="space-y-6">
                <!-- 未確認メンバー -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">未確認メンバー ({{ $unconfirmedMembers->count() }})</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($unconfirmedMembers->count() > 0)
                            <div class="space-y-3">
                                @foreach($unconfirmedMembers as $member)
                                    <div class="flex items-center justify-between p-3 border border-yellow-200 rounded-lg bg-yellow-50">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-yellow-700">
                                                    {{ substr($member->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $member->email ?: '（メールアドレス未登録）' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-yellow-600">確認待ち</span>
                                            @if($travelPlan->created_by === Auth::id())
                                                <form method="POST" action="{{ route('travel-plans.members.confirm', [$travelPlan->uuid, $member->id]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                        確認済みにする
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('travel-plans.members.destroy', [$travelPlan->uuid, $member->id]) }}" 
                                                      onsubmit="return confirm('本当に削除しますか？')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                        削除
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">未確認メンバーはいません</p>
                        @endif
                    </div>
                </div>

                <!-- 招待中 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">招待中 ({{ $pendingInvitations->count() }})</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($pendingInvitations->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingInvitations as $invitation)
                                    <div class="flex items-center justify-between p-3 border border-blue-200 rounded-lg bg-blue-50">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $invitation->invitee_name ?? $invitation->invitee_email }}
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $invitation->invitee_email }}</p>
                                            <p class="text-xs text-gray-400">
                                                有効期限: {{ $invitation->expires_at->format('Y/m/d H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-blue-600">招待中</span>
                                            <button class="text-gray-400 hover:text-gray-600 text-xs" 
                                                    onclick="alert('招待キャンセル機能は今後実装予定です')">
                                                キャンセル
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">送信中の招待はありません</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
            <a href="{{ route('travel-plans.groups.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                グループ管理
            </a>
        </div>
    @endcomponent
@endsection