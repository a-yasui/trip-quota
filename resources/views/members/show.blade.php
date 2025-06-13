<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $member->name }} - {{ $travelPlan->plan_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $member->name }}</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ $travelPlan->plan_name }}</p>
                </div>
                @if($member->user_id !== $travelPlan->owner_user_id)
                    <a href="{{ route('travel-plans.members.edit', [$travelPlan->uuid, $member->id]) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        編集
                    </a>
                @endif
            </div>
        </div>

        <!-- 成功メッセージ -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- メンバー詳細 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 基本情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex items-center mb-6">
                            <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center">
                                <span class="text-xl font-medium text-gray-700">
                                    {{ substr($member->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-medium text-gray-900">{{ $member->name }}</h3>
                                <p class="text-gray-500">{{ $member->email }}</p>
                                <div class="flex items-center space-x-2 mt-2">
                                    @if($travelPlan->owner_user_id === $member->user_id)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                            所有者
                                        </span>
                                    @endif
                                    @if($travelPlan->creator_user_id === $member->user_id)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            作成者
                                        </span>
                                    @endif
                                    @if($member->status === 'CONFIRMED')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            確認済み
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            未確認
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $member->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($member->status === 'CONFIRMED')
                                        <span class="text-green-600">確認済み</span>
                                    @else
                                        <span class="text-yellow-600">未確認</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">参加日</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $member->created_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                            @if($member->confirmed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">確認日</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $member->confirmed_at->format('Y年m月d日 H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- 所属グループ -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">所属グループ</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($member->groups->count() > 0)
                            <div class="space-y-3">
                                @foreach($member->groups as $group)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            @if($group->group_type === 'CORE')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                                    全体
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                                                    班
                                                </span>
                                            @endif
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $group->name }}</p>
                                                @if($group->group_type === 'BRANCH')
                                                    <p class="text-xs text-gray-500">班キー: {{ $group->branch_key }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('travel-plans.groups.show', [$travelPlan->uuid, $group->id]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">所属グループがありません</p>
                        @endif
                    </div>
                </div>

                <!-- アクティビティ履歴 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">アクティビティ履歴</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        <span class="font-medium text-gray-900">メンバーとして参加</span>
                                                    </p>
                                                    <p class="mt-0.5 text-sm text-gray-500">
                                                        {{ $member->created_at->format('Y年m月d日 H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @if($member->confirmed_at)
                                    <li>
                                        <div class="relative pb-8">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            <span class="font-medium text-gray-900">参加を確認</span>
                                                        </p>
                                                        <p class="mt-0.5 text-sm text-gray-500">
                                                            {{ $member->confirmed_at->format('Y年m月d日 H:i') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
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
                        @if($member->user_id !== $travelPlan->owner_user_id)
                            <a href="{{ route('travel-plans.members.edit', [$travelPlan->uuid, $member->id]) }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                                メンバー情報を編集
                            </a>
                        @endif
                        <a href="mailto:{{ $member->email }}" 
                           class="w-full bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-center block">
                            メールを送信
                        </a>
                    </div>
                </div>

                <!-- 権限情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">権限</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">所有者権限</dt>
                                <dd class="text-sm font-medium">
                                    @if($travelPlan->owner_user_id === $member->user_id)
                                        <span class="text-green-600">あり</span>
                                    @else
                                        <span class="text-gray-400">なし</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">作成者権限</dt>
                                <dd class="text-sm font-medium">
                                    @if($travelPlan->creator_user_id === $member->user_id)
                                        <span class="text-blue-600">あり</span>
                                    @else
                                        <span class="text-gray-400">なし</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">メンバー削除</dt>
                                <dd class="text-sm font-medium">
                                    @if($member->user_id !== $travelPlan->owner_user_id)
                                        <span class="text-red-600">可能</span>
                                    @else
                                        <span class="text-gray-400">不可</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
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
                                <dt class="text-sm text-gray-500">所属グループ数</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $member->groups->count() }}個</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">参加からの日数</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $member->created_at->diffInDays(now()) }}日</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('travel-plans.members.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← メンバー一覧に戻る
            </a>
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                旅行プラン詳細
            </a>
        </div>
    </div>
</body>
</html>