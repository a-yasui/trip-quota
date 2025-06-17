@extends('layouts.master')

@section('title', '精算管理 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-6xl'])
        @component('components.page-header', ['title' => '精算管理', 'subtitle' => $travelPlan->plan_name . 'の費用精算を管理します。'])
            <div class="flex items-center space-x-4">
                <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    旅行プラン詳細に戻る
                </a>
                <a href="{{ route('travel-plans.expenses.index', $travelPlan->uuid) }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    費用管理
                </a>
            </div>
        @endcomponent

        @include('components.alerts')

        <!-- 統計情報 -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">精算統計</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">総精算件数</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $statistics['total_settlements'] }}件</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">完了済み</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $statistics['completed_settlements'] }}件</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">未精算</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $statistics['pending_settlements'] }}件</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($statistics['by_currency']))
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">通貨別統計</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($statistics['by_currency'] as $currency => $stats)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="text-lg font-bold text-gray-900">{{ $currency }}</div>
                                    <div class="mt-2 space-y-1">
                                        <div class="text-sm text-gray-600">
                                            総額: {{ number_format($stats['total_amount']) }} {{ $currency }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            未精算: {{ number_format($stats['pending_amount']) }} {{ $currency }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            件数: {{ $stats['count'] }}件
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">精算計算</h3>
                        <p class="text-sm text-gray-500">確定済みの費用を元に精算を自動計算します。</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($statistics['pending_settlements'] > 0)
                            <form method="POST" action="{{ route('travel-plans.settlements.reset', $travelPlan->uuid) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                        onclick="return confirm('未精算の精算情報をリセットしますか？')">
                                    精算をリセット
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('travel-plans.settlements.calculate', $travelPlan->uuid) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                精算を計算
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 精算一覧 -->
        @if($settlements->isEmpty())
            <div class="bg-white shadow-sm rounded-lg">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">精算情報がありません</h3>
                    <p class="mt-1 text-sm text-gray-500">「精算を計算」ボタンから精算計算を実行してください。</p>
                </div>
            </div>
        @else
            @foreach($settlementsByCurrency as $currency => $currencySettlements)
                <div class="bg-white shadow-sm rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">{{ $currency }} 精算</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach($currencySettlements as $settlement)
                            <div class="px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            @if($settlement->is_settled)
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $settlement->payer->name }} → {{ $settlement->payee->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ number_format($settlement->amount) }} {{ $settlement->currency }}
                                                @if($settlement->is_settled)
                                                    • 精算完了 @if($settlement->settled_at)({{ $settlement->settled_at->format('Y/m/d H:i') }})@endif
                                                @else
                                                    • 未精算
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('travel-plans.settlements.show', [$travelPlan->uuid, $settlement->id]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細
                                        </a>
                                        @if(!$settlement->is_settled)
                                            <form method="POST" action="{{ route('travel-plans.settlements.complete', [$travelPlan->uuid, $settlement->id]) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-800 text-sm font-medium"
                                                        onclick="return confirm('この精算を完了として記録しますか？')">
                                                    完了にする
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    @endcomponent
@endsection