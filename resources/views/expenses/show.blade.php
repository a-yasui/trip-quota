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
                        @if($expense->is_split_confirmed)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                確定済み
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                未確定
                            </span>
                        @endif
                        
                        @if(!$expense->is_split_confirmed)
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
                        @endif
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
                                    <div class="flex items-center space-x-2">
                                        @if($split['is_confirmed'])
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
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
            </div>
        </div>

        <!-- アクションボタン -->
        @if(!$expense->is_split_confirmed)
            <div class="mt-6 flex justify-end items-center">
                <div class="flex items-center space-x-3">
                    <!-- 現在のユーザーの参加確認 -->
                    @if($currentUserMember && !$expense->members->where('id', $currentUserMember->id)->first()?->pivot?->is_confirmed)
                        <form method="POST" action="{{ route('travel-plans.expenses.confirm-participation', [$travelPlan->uuid, $expense->id]) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                参加を確認
                            </button>
                        </form>
                    @endif

                    <!-- 費用確定 -->
                    @if(collect($splitAmounts)->every(fn($split) => $split['is_confirmed']))
                        <form method="POST" action="{{ route('travel-plans.expenses.confirm-split', [$travelPlan->uuid, $expense->id]) }}" 
                              onsubmit="return confirm('費用を確定しますか？確定後は編集できなくなります。')" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                費用を確定
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

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