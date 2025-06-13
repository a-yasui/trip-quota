<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ管理 - {{ $travelPlan->plan_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">グループ管理</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ $travelPlan->plan_name }}</p>
                </div>
                <a href="{{ route('travel-plans.groups.create', $travelPlan->uuid) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    班グループを作成
                </a>
            </div>
        </div>

        <!-- 成功メッセージ -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- エラーメッセージ -->
        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- コアグループ -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                            コア
                        </span>
                        {{ $coreGroup ? $coreGroup->name : 'コアグループ' }}
                    </h2>
                </div>
                <div class="px-6 py-4">
                    @if($coreGroup)
                        <p class="text-gray-700 mb-4">{{ $coreGroup->description ?? '全メンバーが参加するメインのグループです' }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                {{ $travelPlan->members->count() }}人のメンバー
                            </span>
                            <a href="{{ route('travel-plans.groups.show', [$travelPlan->uuid, $coreGroup->id]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                詳細を見る
                            </a>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">コアグループが見つかりません</p>
                    @endif
                </div>
            </div>

            <!-- 班グループ一覧 -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">班グループ ({{ $branchGroups->count() }})</h2>
                </div>
                <div class="px-6 py-4">
                    @if($branchGroups->count() > 0)
                        <div class="space-y-4">
                            @foreach($branchGroups as $group)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-md font-medium text-gray-900 flex items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                                    班
                                                </span>
                                                {{ $group->name }}
                                            </h3>
                                            @if($group->description)
                                                <p class="mt-1 text-sm text-gray-600">{{ $group->description }}</p>
                                            @endif
                                            <p class="mt-1 text-xs text-gray-500">キー: {{ $group->branch_key }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('travel-plans.groups.show', [$travelPlan->uuid, $group->id]) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                詳細
                                            </a>
                                            <a href="{{ route('travel-plans.groups.edit', [$travelPlan->uuid, $group->id]) }}" 
                                               class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                                編集
                                            </a>
                                            <form method="POST" action="{{ route('travel-plans.groups.destroy', [$travelPlan->uuid, $group->id]) }}" 
                                                  onsubmit="return confirm('本当に削除しますか？')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    削除
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">班グループがありません</h3>
                                            <p class="mt-1 text-sm text-gray-500">新しい班グループを作成して始めましょう。</p>
                                            <div class="mt-6">
                                                <a href="{{ route('travel-plans.groups.create', $travelPlan->uuid) }}" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    班グループを作成
                                                </a>
                                            </div>
                                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
            <a href="{{ route('travel-plans.members.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                メンバー管理
            </a>
        </div>
    </div>
</body>
</html>