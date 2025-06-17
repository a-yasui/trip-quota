<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ダッシュボード - {{ config('app.name', 'TripQuota') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">{{ config('app.name', 'TripQuota') }}</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">{{ Auth::user()->email }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                            ログアウト
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="px-4 py-6 sm:px-0">
            <div class="border-4 border-dashed border-gray-200 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">ダッシュボード</h2>
                <p class="text-gray-600 mb-6">{{ Auth::user()->email }} としてログインしています。</p>
                
                <!-- 参加している旅行プラン -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">参加している旅行プラン</h3>
                            <a href="{{ route('travel-plans.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                すべて見る →
                            </a>
                        </div>
                        
                        @if($travelPlans->count() > 0)
                            <div class="space-y-4">
                                @foreach($travelPlans->take(3) as $travelPlan)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="hover:text-blue-600">
                                                        {{ $travelPlan->plan_name }}
                                                    </a>
                                                </h4>
                                                <div class="mt-1 flex items-center text-sm text-gray-500 space-x-4">
                                                    <div class="flex items-center">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span>{{ $travelPlan->departure_date->format('Y/m/d') }}</span>
                                                        @if($travelPlan->return_date)
                                                            <span>〜{{ $travelPlan->return_date->format('Y/m/d') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                        <span>{{ $travelPlan->members->where('is_confirmed', true)->count() }}人</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="ml-4 text-blue-600 hover:text-blue-800">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($travelPlans->count() > 3)
                                    <div class="text-center">
                                        <a href="{{ route('travel-plans.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                            他 {{ $travelPlans->count() - 3 }} 件の旅行プランを見る
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">旅行プランがありません</h3>
                                <p class="mt-1 text-sm text-gray-500">新しい旅行プランを作成するか、招待を受けてメンバーになりましょう。</p>
                                <div class="mt-6">
                                    <a href="{{ route('travel-plans.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        新しい旅行プラン
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- クイックアクション -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">クイックアクション</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <a href="{{ route('travel-plans.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                旅行プラン一覧
                            </a>
                            <a href="{{ route('travel-plans.create') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                新しい旅行プラン
                            </a>
                            <a href="#" onclick="alert('旅行プランを選択してからメンバー管理にアクセスしてください')" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                メンバー管理
                            </a>
                            <a href="{{ route('invitations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 relative">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                招待一覧
                                @if($pendingInvitationsCount > 0)
                                    <span class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                        {{ $pendingInvitationsCount }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- アカウント情報 -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">アカウント情報</h3>
                        @if(Auth::user()->accounts->count() > 0)
                            <div class="space-y-2">
                                @foreach(Auth::user()->accounts as $account)
                                    <div class="flex items-center space-x-3">
                                        @if($account->thumbnail_url)
                                            <img src="{{ $account->thumbnail_url }}" alt="{{ $account->display_name }}" class="w-8 h-8 rounded-full">
                                        @else
                                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">{{ substr($account->account_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $account->display_name }}</p>
                                            <p class="text-sm text-gray-500">@{{ $account->account_name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">アカウントが設定されていません。</p>
                        @endif
                    </div>
                </div>

                <!-- OAuth連携情報 -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">OAuth連携</h3>
                        @if(Auth::user()->oauthProviders->count() > 0)
                            <div class="space-y-2">
                                @foreach(Auth::user()->oauthProviders as $provider)
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($provider->provider) }}
                                        </span>
                                        <span class="text-sm text-gray-600">連携済み</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">OAuth連携がありません。</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>