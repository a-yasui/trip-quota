@extends('layouts.master')

@section('title', $group->name . ' - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-5xl'])
        @component('components.page-header')
            @slot('title')
                @if($group->type === 'CORE')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 mr-3">
                        全体
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mr-3">
                        班
                    </span>
                @endif
                {{ $group->name }}
            @endslot
            @slot('subtitle'){{ $travelPlan->plan_name }}@endslot
            @slot('action')
                @if($group->group_type === 'BRANCH')
                    <a href="{{ route('travel-plans.groups.edit', [$travelPlan->uuid, $group->id]) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        編集
                    </a>
                @endif
            @endslot
        @endcomponent

        @include('components.alerts')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- グループ詳細 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 基本情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">グループ名</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $group->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">グループタイプ</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($group->group_type === 'CORE')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            全体グループ
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            班グループ
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            @if($group->group_type === 'BRANCH')
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">班キー</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $group->branch_key }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">メンバー数</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $group->members->count() }}人</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">作成日</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $group->created_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- 説明 -->
                @if($group->description)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">説明</h2>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $group->description }}</p>
                        </div>
                    </div>
                @endif

                <!-- メンバー一覧 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">メンバー一覧</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($group->members->count() > 0)
                            <div class="space-y-3">
                                @foreach($group->members as $member)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($member->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                                <div class="flex items-center space-x-2 mt-1">
                                                    @if($travelPlan->owner_user_id === $member->user_id)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            所有者
                                                        </span>
                                                    @endif
                                                    @if($travelPlan->creator_user_id === $member->user_id)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            作成者
                                                        </span>
                                                    @endif
                                                    @if($member->is_confirmed)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            確認済み
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            未確認
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ route('travel-plans.members.show', [$travelPlan->uuid, $member->id]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">このグループにはメンバーがいません</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- サイドバー -->
            <div class="space-y-6">
                <!-- アクション -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">アクション</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        @if($currentMember && !$isCurrentUserInGroup)
                            <form action="{{ route('travel-plans.groups.join', [$travelPlan->uuid, $group->id]) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    このグループに参加する
                                </button>
                            </form>
                        @elseif($currentMember && $isCurrentUserInGroup)
                            <div class="w-full bg-gray-100 text-gray-600 px-4 py-2 rounded-md text-sm font-medium text-center">
                                参加中
                            </div>
                        @endif
                        
                        <a href="{{ route('travel-plans.members.create', $travelPlan->uuid) }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                            メンバーを招待
                        </a>
                        @if($group->group_type === 'BRANCH')
                            <a href="{{ route('travel-plans.groups.edit', [$travelPlan->uuid, $group->id]) }}" 
                               class="w-full bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-center block">
                                グループ情報を編集
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 統計情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">統計</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">総メンバー数</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $group->members->count() }}人</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">確認済み</dt>
                                <dd class="text-sm font-medium text-green-600">{{ $group->members->where('is_confirmed', true)->count() }}人</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">未確認</dt>
                                <dd class="text-sm font-medium text-yellow-600">{{ $group->members->where('is_confirmed', false)->count() }}人</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('travel-plans.groups.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← グループ一覧に戻る
            </a>
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                旅行プラン詳細
            </a>
        </div>
    @endcomponent
@endsection