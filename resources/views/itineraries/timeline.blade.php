@extends('layouts.master')

@section('title', '旅程タイムライン - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-7xl'])
        @component('components.page-header', ['title' => '旅程タイムライン', 'subtitle' => $travelPlan->plan_name . 'のスケジュール'])
            @slot('action')
                <div class="flex space-x-3">
                    <a href="{{ route('travel-plans.itineraries.index', $travelPlan->uuid) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        リスト表示
                    </a>
                    <a href="{{ route('travel-plans.itineraries.create', $travelPlan->uuid) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        旅程を追加
                    </a>
                </div>
            @endslot
        @endcomponent

        @include('components.alerts')

        <!-- 日付範囲フィルター -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                        <input type="date" name="start_date" id="start_date" 
                               value="{{ request('start_date', $startDate->format('Y-m-d')) }}" 
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex-1 min-w-0">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                        <input type="date" name="end_date" id="end_date" 
                               value="{{ request('end_date', $endDate->format('Y-m-d')) }}" 
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex space-x-2 pt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            期間変更
                        </button>
                        <a href="{{ route('travel-plans.itineraries.timeline', $travelPlan->uuid) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            リセット
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- タイムライン表示 -->
        @if($itinerariesByDate->count() > 0)
            <div class="space-y-8">
                @foreach($itinerariesByDate as $date => $dayItineraries)
                    @php
                        $dateObj = \Carbon\Carbon::parse($date);
                        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
                    @endphp
                    
                    <div class="bg-white shadow-sm rounded-lg">
                        <!-- 日付ヘッダー -->
                        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200 rounded-t-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-blue-900">
                                        {{ $dateObj->format('n月d日') }}（{{ $dayOfWeek }}）
                                    </h2>
                                    <p class="text-sm text-blue-700 mt-1">{{ $dayItineraries->count() }}件の旅程</p>
                                </div>
                                <a href="{{ route('travel-plans.itineraries.create', $travelPlan->uuid) }}?date={{ $date }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium">
                                    この日に追加
                                </a>
                            </div>
                        </div>

                        <!-- 旅程リスト -->
                        <div class="divide-y divide-gray-200">
                            @foreach($dayItineraries->sortBy(['start_time', 'created_at']) as $itinerary)
                                <div class="px-6 py-4 hover:bg-gray-50">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <!-- 時刻表示 -->
                                                <div class="w-24 flex-shrink-0">
                                                    @if($itinerary->start_time)
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $itinerary->start_time->format('H:i') }}
                                                        </div>
                                                        @if($itinerary->end_time)
                                                            <div class="text-xs text-gray-500">
                                                                ～{{ $itinerary->end_time->format('H:i') }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="text-sm text-gray-400">時間未定</div>
                                                    @endif
                                                </div>

                                                <!-- 交通手段アイコン -->
                                                <div class="w-8 h-8 mx-4 flex-shrink-0 rounded-full flex items-center justify-center {{ $itinerary->transportation_type ? 'bg-blue-100' : 'bg-gray-100' }}">
                                                    @switch($itinerary->transportation_type)
                                                        @case('airplane')
                                                            <span class="text-blue-600">✈️</span>
                                                            @break
                                                        @case('car')
                                                            <span class="text-blue-600">🚗</span>
                                                            @break
                                                        @case('bus')
                                                            <span class="text-blue-600">🚌</span>
                                                            @break
                                                        @case('ferry')
                                                            <span class="text-blue-600">⛴️</span>
                                                            @break
                                                        @case('bike')
                                                            <span class="text-blue-600">🚲</span>
                                                            @break
                                                        @case('walking')
                                                            <span class="text-blue-600">🚶</span>
                                                            @break
                                                        @default
                                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                    @endswitch
                                                </div>

                                                <!-- 旅程内容 -->
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-lg font-medium text-gray-900 truncate">
                                                        <a href="{{ route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id]) }}" 
                                                           class="hover:text-blue-600">
                                                            {{ $itinerary->title }}
                                                        </a>
                                                    </h3>
                                                    
                                                    <div class="mt-1 flex items-center flex-wrap gap-3 text-sm text-gray-500">
                                                        @if($itinerary->group)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $itinerary->group->type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                                @if($itinerary->group->type === 'CORE')
                                                                    [全体] {{ $itinerary->group->name }}
                                                                @else
                                                                    [班] {{ $itinerary->group->name }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                        
                                                        @if($itinerary->departure_location || $itinerary->arrival_location)
                                                            <span class="flex items-center">
                                                                @if($itinerary->departure_location)
                                                                    {{ $itinerary->departure_location }}
                                                                @endif
                                                                @if($itinerary->departure_location && $itinerary->arrival_location)
                                                                    →
                                                                @endif
                                                                @if($itinerary->arrival_location)
                                                                    {{ $itinerary->arrival_location }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                        
                                                        @if($itinerary->members->count() > 0)
                                                            <span class="flex items-center">
                                                                <svg class="flex-shrink-0 mr-1 h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                                </svg>
                                                                {{ $itinerary->members->count() }}人参加
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($itinerary->description)
                                                        <p class="mt-2 text-sm text-gray-700 line-clamp-2">
                                                            {{ Str::limit($itinerary->description, 150) }}
                                                        </p>
                                                    @endif

                                                    @if($itinerary->flight_number)
                                                        <div class="mt-2 text-sm text-blue-600 font-medium">
                                                            {{ $itinerary->airline ? $itinerary->airline . ' ' : '' }}{{ $itinerary->flight_number }}
                                                            @if($itinerary->departure_time && $itinerary->arrival_time)
                                                                <span class="text-gray-500 ml-2">
                                                                    {{ $itinerary->departure_time->format('H:i') }} - {{ $itinerary->arrival_time->format('H:i') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- アクションボタン -->
                                        <div class="flex items-center space-x-2 ml-4">
                                            <a href="{{ route('travel-plans.itineraries.edit', [$travelPlan->uuid, $itinerary->id]) }}" 
                                               class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                編集
                                            </a>
                                            <form method="POST" action="{{ route('travel-plans.itineraries.destroy', [$travelPlan->uuid, $itinerary->id]) }}" 
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
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">指定期間に旅程がありません</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $startDate->format('Y年n月d日') }} から {{ $endDate->format('Y年n月d日') }} の期間に旅程がありません。
                </p>
                <div class="mt-6">
                    <a href="{{ route('travel-plans.itineraries.create', $travelPlan->uuid) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        旅程を追加
                    </a>
                </div>
            </div>
        @endif

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
            <a href="{{ route('travel-plans.groups.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                グループ管理
            </a>
            <a href="{{ route('travel-plans.members.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                メンバー管理
            </a>
        </div>
    @endcomponent
@endsection

@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush