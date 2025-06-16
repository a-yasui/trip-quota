@extends('layouts.master')

@section('title', '精算詳細 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => '精算詳細', 'subtitle' => $travelPlan->plan_name . 'の精算詳細を表示します。'])
            <div class="flex items-center space-x-4">
                <a href="{{ route('travel-plans.settlements.index', $travelPlan->uuid) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    精算管理に戻る
                </a>
            </div>
        @endcomponent

        @include('components.alerts')

        <!-- 精算詳細 -->
        <div class="bg-white shadow-sm rounded-lg">
            <!-- ヘッダー -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">精算情報</h3>
                    <div class="flex items-center">
                        @if($settlement->is_settled)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"/>
                                </svg>
                                精算完了
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"/>
                                </svg>
                                未精算
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 詳細情報 -->
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- 支払い者 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">支払い者</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $settlement->payer->name }}</dd>
                    </div>

                    <!-- 受取者 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">受取者</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $settlement->payee->name }}</dd>
                    </div>

                    <!-- 精算金額 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">精算金額</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">
                            {{ number_format($settlement->amount) }} {{ $settlement->currency }}
                        </dd>
                    </div>

                    <!-- 作成日時 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成日時</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $settlement->created_at->format('Y年n月d日 H:i') }}</dd>
                    </div>

                    @if($settlement->is_settled && $settlement->settled_at)
                        <!-- 精算完了日時 -->
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">精算完了日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $settlement->settled_at->format('Y年n月d日 H:i') }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 精算の流れ説明 -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">精算について</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>
                            この精算は、旅行中の費用を基に自動計算されました。
                            <strong>{{ $settlement->payer->name }}</strong>さんが
                            <strong>{{ $settlement->payee->name }}</strong>さんに
                            <strong>{{ number_format($settlement->amount) }} {{ $settlement->currency }}</strong>
                            をお支払いください。
                        </p>
                        @if(!$settlement->is_settled)
                            <p class="mt-2">
                                実際にお金の受け渡しが完了したら、「精算完了」ボタンで記録してください。
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- アクションボタン -->
        @if(!$settlement->is_settled)
            <div class="mt-6 flex items-center justify-end space-x-4">
                <form method="POST" action="{{ route('travel-plans.settlements.complete', [$travelPlan->uuid, $settlement->id]) }}">
                    @csrf
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors"
                            onclick="return confirm('この精算を完了として記録しますか？実際にお金の受け渡しが完了している場合のみ実行してください。')">
                        精算完了
                    </button>
                </form>
            </div>
        @endif
    @endcomponent
@endsection