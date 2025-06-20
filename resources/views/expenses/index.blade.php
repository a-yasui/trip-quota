@extends('layouts.master')

@section('title', '費用管理 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-6xl'])
        @component('components.page-header', ['title' => '費用管理', 'subtitle' => $travelPlan->plan_name . 'の費用一覧です。'])
        @endcomponent

        @include('components.alerts')

        <!-- 統計情報 -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- 総費用 -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">総費用</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    @if($amountsByCurrency->count() > 1)
                                        複数通貨
                                    @else
                                        {{ number_format($totalAmount) }} {{ $expenses->first()->currency ?? 'JPY' }}
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 費用件数 -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">費用件数</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $expenses->count() }}件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 通貨別集計 -->
        @if($amountsByCurrency->count() > 1)
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">通貨別集計</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($amountsByCurrency as $currency => $amount)
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($amount) }}</div>
                                <div class="text-sm text-gray-500">{{ $currency }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- 精算サマリー -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">精算サマリー</h2>
                    <a href="{{ route('travel-plans.settlements.index', $travelPlan->uuid) }}" 
                       class="text-sm text-blue-600 hover:text-blue-800">
                        詳細を見る →
                    </a>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $settlementStats['total_settlements'] }}</div>
                        <div class="text-sm text-gray-500">総精算件数</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $settlementStats['completed_settlements'] }}</div>
                        <div class="text-sm text-gray-500">精算済み</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $settlementStats['pending_settlements'] }}</div>
                        <div class="text-sm text-gray-500">未精算</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">アクション</div>
                        <a href="{{ route('travel-plans.settlements.index', $travelPlan->uuid) }}" 
                           class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            精算計算
                        </a>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-500 text-center">
                    確定済みの費用から自動的に精算を計算できます。詳細は精算管理画面をご確認ください。
                </p>
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="mt-6 flex justify-between items-center">
            <a href="{{ route('travel-plans.settlements.index', $travelPlan->uuid) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                精算管理
            </a>
            <a href="{{ route('travel-plans.expenses.create', $travelPlan->uuid) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                費用を追加
            </a>
        </div>

        <!-- 費用一覧 -->
        @if($expenses->count() > 0)
            <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">費用一覧</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($expenses as $expense)
                        <div class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <h3 class="text-sm font-medium text-gray-900">{{ $expense->title }}</h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    精算対象
                                                </span>
                                            </div>
                                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                                <span>{{ $expense->expense_date->format('Y/m/d') }}</span>
                                                <span>{{ $expense->group->name }}</span>
                                                <span>支払い: {{ $expense->paidBy->name }}</span>
                                                @if($expense->description)
                                                    <span>{{ Str::limit($expense->description, 50) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-semibold text-gray-900">
                                                {{ number_format($expense->amount) }} {{ $expense->currency }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $expense->members->count() }}人で分割
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                    <a href="{{ route('travel-plans.expenses.show', [$travelPlan->uuid, $expense->id]) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        詳細
                                    </a>
                                    <a href="{{ route('travel-plans.expenses.edit', [$travelPlan->uuid, $expense->id]) }}" 
                                       class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                        編集
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- 空状態 -->
            <div class="mt-6 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">費用がありません</h3>
                <p class="mt-1 text-sm text-gray-500">最初の費用を追加してみましょう。</p>
                <div class="mt-6">
                    <a href="{{ route('travel-plans.expenses.create', $travelPlan->uuid) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        費用を追加
                    </a>
                </div>
            </div>
        @endif

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
        </div>
    @endcomponent
@endsection