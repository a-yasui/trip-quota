<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $travelPlan->plan_name }} - TripQuota</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $travelPlan->plan_name }}</h1>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>
                            {{ $travelPlan->departure_date->format('Y年n月d日') }}
                            @if($travelPlan->return_date)
                                〜 {{ $travelPlan->return_date->format('Y年n月d日') }}
                            @endif
                        </span>
                        <span class="ml-4">{{ $travelPlan->timezone }}</span>
                    </div>
                    @if(!$travelPlan->is_active)
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                無効
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('travel-plans.edit', $travelPlan->uuid) }}" 
                       class="bg-white hover:bg-gray-50 text-gray-700 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium">
                        編集
                    </a>
                    @if($travelPlan->owner_user_id === auth()->id())
                        <form method="POST" action="{{ route('travel-plans.destroy', $travelPlan->uuid) }}" 
                              onsubmit="return confirm('本当に削除しますか？この操作は取り消せません。')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                削除
                            </button>
                        </form>
                    @endif
                </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- メインコンテンツ -->
            <div class="lg:col-span-2 space-y-8">
                <!-- 旅行プラン詳細 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">旅行プラン詳細</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($travelPlan->description)
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $travelPlan->description }}</p>
                        @else
                            <p class="text-gray-500 italic">説明はありません</p>
                        @endif
                    </div>
                </div>

                <!-- グループ一覧 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">グループ</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($travelPlan->groups->count() > 0)
                            <div class="space-y-4">
                                @foreach($travelPlan->groups as $group)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="text-md font-medium text-gray-900">
                                                    {{ $group->name }}
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $group->type === 'CORE' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                        {{ $group->type === 'CORE' ? 'コア' : '班' }}
                                                    </span>
                                                </h3>
                                                @if($group->description)
                                                    <p class="mt-1 text-sm text-gray-600">{{ $group->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">グループはまだ作成されていません。</p>
                        @endif
                    </div>
                </div>

                <!-- 管理機能 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">管理機能</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- 実装済み機能 -->
                            <div class="space-y-3">
                                <!-- グループ管理 -->
                                <a href="{{ route('travel-plans.groups.index', $travelPlan->uuid) }}" 
                                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">グループ管理</h3>
                                        <p class="text-sm text-gray-500">旅行グループと班の管理</p>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <!-- メンバー管理 -->
                                <a href="{{ route('travel-plans.members.index', $travelPlan->uuid) }}" 
                                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">メンバー管理</h3>
                                        <p class="text-sm text-gray-500">参加者の招待と管理</p>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <!-- 旅程管理 -->
                                <a href="{{ route('travel-plans.itineraries.index', $travelPlan->uuid) }}" 
                                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">旅程管理</h3>
                                        <p class="text-sm text-gray-500">行程・スケジュールの管理</p>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <!-- 宿泊施設管理 -->
                                <a href="{{ route('travel-plans.accommodations.index', $travelPlan->uuid) }}" 
                                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 8v-1a1 1 0 011-1h1a1 1 0 011 1v1m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">宿泊施設管理</h3>
                                        <p class="text-sm text-gray-500">ホテル・宿泊施設の管理</p>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>

                                <!-- 費用管理 -->
                                <a href="{{ route('travel-plans.expenses.index', $travelPlan->uuid) }}" 
                                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">費用管理</h3>
                                        <p class="text-sm text-gray-500">割り勘・精算管理</p>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>

                            <!-- 未実装機能 -->
                            <div class="mt-6">
                                <h3 class="text-sm font-medium text-gray-500 mb-3">今後の機能</h3>
                                <div class="space-y-3">

                                    <!-- 書類管理 -->
                                    <div class="flex items-center p-4 border border-gray-200 rounded-lg bg-gray-50 cursor-not-allowed">
                                        <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-sm font-medium text-gray-500">書類管理（未実装）</h3>
                                            <p class="text-sm text-gray-400">旅行関連書類の管理</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- サイドバー -->
            <div class="space-y-6">
                <!-- 基本情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">作成者</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $travelPlan->creator->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">所有者</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $travelPlan->owner->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">UUID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $travelPlan->uuid }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">作成日</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $travelPlan->created_at->format('Y年n月d日 H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $travelPlan->updated_at->format('Y年n月d日 H:i') }}</dd>
                        </div>
                    </div>
                </div>

                <!-- メンバー一覧 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">メンバー ({{ $travelPlan->members->count() }}人)</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($travelPlan->members->count() > 0)
                            <div class="space-y-3">
                                @foreach($travelPlan->members as $member)
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ substr($member->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                            @if(!$member->is_confirmed)
                                                <p class="text-xs text-gray-500">未確認</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">メンバーはいません。</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="{{ route('travel-plans.index') }}" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン一覧に戻る
            </a>
        </div>
    </div>
</body>
</html>