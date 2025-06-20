@extends('layouts.master')

@section('title', $expense->title . ' - 費用詳細')

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => $expense->title, 'subtitle' => '費用詳細'])
        @endcomponent

        @include('components.alerts')

        <!-- 費用基本情報 -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('travel-plans.expenses.edit', [$travelPlan->uuid, $expense->id]) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                            編集
                        </a>
                        <form method="POST" action="{{ route('travel-plans.expenses.destroy', [$travelPlan->uuid, $expense->id]) }}" 
                              onsubmit="return confirm('本当に削除しますか？この操作は取り消せません。')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- 費用タイトル -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">費用タイトル</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $expense->title }}</dd>
                    </div>

                    <!-- 金額 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">金額</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">
                            {{ number_format($expense->amount) }} {{ $expense->currency }}
                        </dd>
                    </div>

                    <!-- 費用日付 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">費用日付</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $expense->expense_date->format('Y年n月d日') }}</dd>
                    </div>

                    <!-- 対象グループ -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">対象グループ</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $expense->group->name }}</dd>
                    </div>

                    <!-- 支払い者 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">支払い者</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $expense->paidBy->name }}</dd>
                    </div>

                    <!-- 参加者数 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">参加者数</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $expense->members->where('pivot.is_participating', true)->count() }}人</dd>
                    </div>
                </div>

                <!-- 説明 -->
                @if($expense->description)
                    <div class="mt-6">
                        <dt class="text-sm font-medium text-gray-500">説明・詳細</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $expense->description }}</dd>
                    </div>
                @endif
            </div>
        </div>

        <!-- 分割詳細 -->
        <div class="mt-6 bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">分割詳細</h2>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    @foreach($splitAmounts as $split)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg {{ $split['member']->id === $currentUserMember?->id ? 'bg-blue-50 border-blue-200' : '' }}">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ substr($split['member']->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $split['member']->name }}
                                        @if($split['member']->id === $currentUserMember?->id)
                                            <span class="text-blue-600">(あなた)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-semibold text-gray-900">
                                    {{ number_format($split['amount']) }} {{ $expense->currency }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 分割計算サマリー -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">総金額:</span>
                            <span class="font-semibold ml-2">{{ number_format($expense->amount) }} {{ $expense->currency }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">参加者数:</span>
                            <span class="font-semibold ml-2">{{ count($splitAmounts) }}人</span>
                        </div>
                        <div>
                            <span class="text-gray-500">1人あたり:</span>
                            <span class="font-semibold ml-2">
                                @if(count($splitAmounts) > 0)
                                    {{ number_format($expense->amount / count($splitAmounts)) }} {{ $expense->currency }}
                                @else
                                    0 {{ $expense->currency }}
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">確認済み:</span>
                            <span class="font-semibold ml-2">{{ collect($splitAmounts)->where('is_confirmed', true)->count() }}人</span>
                        </div>
                    </div>
                </div>

                <!-- 分割金額編集フォーム -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-900 mb-4">分割金額を調整</h3>
                    <form method="POST" action="{{ route('travel-plans.expenses.update-splits', [$travelPlan->uuid, $expense->id]) }}">
                        @csrf
                        <div class="space-y-3">
                            @foreach($splitAmounts as $index => $split)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $split['member']->name }}</span>
                                        @if($split['member']->id === $currentUserMember?->id)
                                            <span class="text-xs text-blue-600">(あなた)</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="hidden" name="splits[{{ $index }}][member_id]" value="{{ $split['member']->id }}">
                                        <input type="number" 
                                               name="splits[{{ $index }}][amount]" 
                                               value="{{ $split['amount'] }}"
                                               step="0.01"
                                               min="0"
                                               class="w-24 px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <span class="text-sm text-gray-500">{{ $expense->currency }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <span id="split-total">{{ number_format($expense->amount) }}</span> / {{ number_format($expense->amount) }} {{ $expense->currency }}
                                </div>
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                                    分割金額を更新
                                </button>
                            </div>
                        </form>
                    </div>

                    <script>
                        // 分割金額の合計をリアルタイム計算
                        document.addEventListener('DOMContentLoaded', function() {
                            const amountInputs = document.querySelectorAll('input[name*="[amount]"]');
                            const totalElement = document.getElementById('split-total');
                            
                            function updateTotal() {
                                let total = 0;
                                amountInputs.forEach(input => {
                                    total += parseFloat(input.value) || 0;
                                });
                                totalElement.textContent = total.toLocaleString();
                                
                                // 合計が一致しない場合は色を変える
                                const expectedTotal = {{ $expense->amount }};
                                if (Math.abs(total - expectedTotal) > 0.01) {
                                    totalElement.className = 'text-red-600 font-semibold';
                                } else {
                                    totalElement.className = 'text-green-600 font-semibold';
                                }
                            }
                            
                            amountInputs.forEach(input => {
                                input.addEventListener('input', updateTotal);
                            });
                            
                            updateTotal();
                        });
                    </script>
            </div>
        </div>


        <!-- 作成情報 -->
        <div class="mt-6 bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">作成情報</h2>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成日時</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $expense->created_at->format('Y年n月d日 H:i') }}</dd>
                    </div>
                    @if($expense->created_at != $expense->updated_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $expense->updated_at->format('Y年n月d日 H:i') }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="{{ route('travel-plans.expenses.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← 費用一覧に戻る
            </a>
        </div>
    @endcomponent
@endsection