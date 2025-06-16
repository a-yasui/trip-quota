@extends('layouts.master')

@section('title', $accommodation->name . ' - 宿泊施設詳細')

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => $accommodation->name, 'subtitle' => '宿泊施設詳細'])
        @endcomponent

        @include('components.alerts')

        <!-- 宿泊施設詳細情報 -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('travel-plans.accommodations.edit', [$travelPlan->uuid, $accommodation->id]) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                            編集
                        </a>
                        <form method="POST" action="{{ route('travel-plans.accommodations.destroy', [$travelPlan->uuid, $accommodation->id]) }}" 
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
                    <!-- 宿泊施設名 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">宿泊施設名</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $accommodation->name }}</dd>
                    </div>

                    <!-- 住所 -->
                    @if($accommodation->address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">住所</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accommodation->address }}</dd>
                        </div>
                    @endif

                    <!-- チェックイン日 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">チェックイン日</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $accommodation->check_in_date->format('Y年n月d日') }}
                            @if($accommodation->check_in_time)
                                {{ $accommodation->check_in_time->format('H:i') }}
                            @endif
                        </dd>
                    </div>

                    <!-- チェックアウト日 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">チェックアウト日</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $accommodation->check_out_date->format('Y年n月d日') }}
                            @if($accommodation->check_out_time)
                                {{ $accommodation->check_out_time->format('H:i') }}
                            @endif
                        </dd>
                    </div>

                    <!-- 宿泊期間 -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">宿泊期間</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $accommodation->check_in_date->diffInDays($accommodation->check_out_date) }}泊
                        </dd>
                    </div>

                    <!-- 料金 -->
                    @if($accommodation->price_per_night)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">1泊あたりの料金</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ number_format($accommodation->price_per_night) }} {{ $accommodation->currency }}
                            </dd>
                        </div>

                        <!-- 総料金 -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">総料金</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                {{ number_format($accommodation->price_per_night * $accommodation->check_in_date->diffInDays($accommodation->check_out_date)) }} {{ $accommodation->currency }}
                            </dd>
                        </div>
                    @endif

                    <!-- 予約番号 -->
                    @if($accommodation->confirmation_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">予約番号・確認番号</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $accommodation->confirmation_number }}</dd>
                        </div>
                    @endif
                </div>

                <!-- メモ・備考 -->
                @if($accommodation->notes)
                    <div class="mt-6">
                        <dt class="text-sm font-medium text-gray-500">メモ・備考</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $accommodation->notes }}</dd>
                    </div>
                @endif
            </div>
        </div>

        <!-- 宿泊メンバー -->
        @if($accommodation->members->count() > 0)
            <div class="mt-6 bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">宿泊メンバー ({{ $accommodation->members->count() }}人)</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($accommodation->members as $member)
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
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
                        <dt class="text-sm font-medium text-gray-500">作成者</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $accommodation->createdBy->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成日時</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $accommodation->created_at->format('Y年n月d日 H:i') }}</dd>
                    </div>
                    @if($accommodation->created_at != $accommodation->updated_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $accommodation->updated_at->format('Y年n月d日 H:i') }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-6 flex justify-center">
            <a href="{{ route('travel-plans.accommodations.index', $travelPlan->uuid) }}" 
               class="text-blue-600 hover:text-blue-800">
                ← 宿泊施設一覧に戻る
            </a>
        </div>
    @endcomponent
@endsection