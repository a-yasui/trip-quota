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

        <!-- 表示モード切り替え -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">表示モード</h3>
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button id="list-view-btn" class="px-4 py-2 text-sm font-medium rounded-md transition-colors">
                            リスト表示
                        </button>
                        <button id="timeline-view-btn" class="px-4 py-2 text-sm font-medium rounded-md transition-colors">
                            タイムライン表示
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- タイムライン表示 -->
        @if($itinerariesByDate->count() > 0)
            <!-- リストビュー -->
            <div id="list-view" class="space-y-8">
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
                                                    @if($itinerary->transportation_type)
                                                        <span class="text-blue-600">{{ $itinerary->transportation_icon }}</span>
                                                    @else
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    @endif
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
                                                        
                                                        @if($itinerary->route_info)
                                                            <span class="flex items-center text-gray-600 bg-gray-100 px-2 py-1 rounded">
                                                                {{ $itinerary->route_info }}
                                                            </span>
                                                        @elseif($itinerary->departure_location || $itinerary->arrival_location)
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

                                                    @if($itinerary->transportation_summary)
                                                        <div class="mt-2 text-sm text-blue-600 font-medium">
                                                            {{ $itinerary->transportation_summary }}
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

            <!-- タイムラインビュー -->
            <div id="timeline-view" class="hidden space-y-8">
                @foreach($itinerariesByDate as $date => $dayItineraries)
                    @php
                        $dateObj = \Carbon\Carbon::parse($date);
                        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
                        
                        // 時間ごとにグループ化
                        $timedItineraries = $dayItineraries->filter(fn($i) => $i->start_time)
                            ->sortBy('start_time')
                            ->groupBy(function($itinerary) {
                                return $itinerary->start_time->format('H:i');
                            });
                        $untimedItineraries = $dayItineraries->filter(fn($i) => !$i->start_time);
                        
                        // 時間軸の設定 (6:00 - 23:59)
                        $timeSlots = [];
                        for($hour = 6; $hour <= 23; $hour++) {
                            $timeSlots[] = sprintf('%02d:00', $hour);
                            if($hour < 23) {
                                $timeSlots[] = sprintf('%02d:30', $hour);
                            }
                        }
                    @endphp
                    
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <!-- 日付ヘッダー -->
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-blue-900">
                                        {{ $dateObj->format('n月d日') }}（{{ $dayOfWeek }}）
                                    </h2>
                                    <p class="text-sm text-blue-700 mt-1">{{ $dayItineraries->count() }}件の旅程</p>
                                </div>
                                <a href="{{ route('travel-plans.itineraries.create', $travelPlan->uuid) }}?date={{ $date }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                    この日に追加
                                </a>
                            </div>
                        </div>

                        <!-- タイムライングリッド -->
                        <div class="timeline-container" style="min-height: 400px;">
                            <!-- 時間軸 -->
                            <div class="flex bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                                <div class="w-20 flex-shrink-0 bg-gray-100 border-r border-gray-200 p-2">
                                    <div class="text-xs font-medium text-gray-500 text-center">時刻</div>
                                </div>
                                <div class="flex-1 relative">
                                    <div class="flex h-12">
                                        @foreach($timeSlots as $time)
                                            <div class="flex-1 border-r border-gray-200 p-2 text-center">
                                                <div class="text-xs font-medium text-gray-700">{{ $time }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- レーン表示エリア -->
                            <div class="relative" style="min-height: 300px;">
                                <!-- 時間グリッド線 -->
                                <div class="absolute inset-0 flex">
                                    <div class="w-20 flex-shrink-0 bg-gray-50 border-r border-gray-200"></div>
                                    <div class="flex-1 flex">
                                        @foreach($timeSlots as $time)
                                            <div class="flex-1 border-r border-gray-100"></div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- 旅程アイテム -->
                                @php $laneIndex = 0; @endphp
                                @foreach($timedItineraries as $time => $timeGroup)
                                    @foreach($timeGroup as $itinerary)
                                        @php
                                            // 時間位置の計算 (6:00を基準とする)
                                            $startHour = $itinerary->start_time->hour;
                                            $startMinute = $itinerary->start_time->minute;
                                            $startPosition = (($startHour - 6) * 2) + ($startMinute >= 30 ? 1 : 0);
                                            
                                            // 終了時間がある場合の幅計算
                                            $width = 1; // デフォルトは30分幅
                                            if($itinerary->end_time) {
                                                $endHour = $itinerary->end_time->hour;
                                                $endMinute = $itinerary->end_time->minute;
                                                $endPosition = (($endHour - 6) * 2) + ($endMinute >= 30 ? 1 : 0);
                                                $width = max(1, $endPosition - $startPosition);
                                            }
                                            
                                            $leftPercent = ($startPosition / count($timeSlots)) * 100;
                                            $widthPercent = ($width / count($timeSlots)) * 100;
                                        @endphp
                                        
                                        <div class="absolute timeline-item" 
                                             style="top: {{ 60 + ($laneIndex * 80) }}px; left: calc(5rem + {{ $leftPercent }}%); width: {{ $widthPercent }}%; height: 60px;">
                                            <div class="bg-gradient-to-r {{ $itinerary->group && $itinerary->group->type === 'CORE' ? 'from-green-400 to-green-500' : 'from-blue-400 to-blue-500' }} 
                                                        text-white rounded-lg shadow-md border border-white p-3 h-full flex items-center hover:shadow-lg transition-shadow cursor-pointer"
                                                 onclick="window.location.href='{{ route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id]) }}'">
                                                <div class="flex items-center space-x-2 min-w-0 w-full">
                                                    <!-- 交通手段アイコン -->
                                                    <div class="flex-shrink-0">
                                                        @if($itinerary->transportation_type)
                                                            <span class="text-white text-sm">{{ $itinerary->transportation_icon }}</span>
                                                        @else
                                                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- 旅程情報 -->
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-sm font-medium truncate">{{ $itinerary->title }}</div>
                                                        @if($itinerary->route_info)
                                                            <div class="text-xs opacity-90 truncate">{{ $itinerary->route_info }}</div>
                                                        @endif
                                                        @if($itinerary->transportation_summary)
                                                            <div class="text-xs opacity-75 truncate">{{ $itinerary->transportation_summary }}</div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- 時間表示 -->
                                                    <div class="flex-shrink-0 text-right">
                                                        <div class="text-xs font-medium">{{ $itinerary->start_time->format('H:i') }}</div>
                                                        @if($itinerary->end_time)
                                                            <div class="text-xs opacity-75">{{ $itinerary->end_time->format('H:i') }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $laneIndex++; @endphp
                                    @endforeach
                                @endforeach
                                
                                <!-- 時間未定の旅程 -->
                                @if($untimedItineraries->count() > 0)
                                    <div class="absolute" style="top: {{ 60 + ($laneIndex * 80) + 20 }}px; left: 5rem; right: 1rem;">
                                        <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-4">
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">時間未定の旅程</h4>
                                            <div class="space-y-2">
                                                @foreach($untimedItineraries as $itinerary)
                                                    <div class="bg-white rounded p-2 border border-gray-200 hover:bg-gray-50 cursor-pointer"
                                                         onclick="window.location.href='{{ route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id]) }}'">
                                                        <div class="flex items-center space-x-2">
                                                            <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $itinerary->transportation_type ? 'bg-blue-100' : 'bg-gray-100' }}">
                                                                @if($itinerary->transportation_type)
                                                                    <span class="text-blue-600 text-xs">{{ $itinerary->transportation_icon }}</span>
                                                                @else
                                                                    <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                            <span class="text-sm font-medium text-gray-900">{{ $itinerary->title }}</span>
                                                            @if($itinerary->group)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $itinerary->group->type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                                    @if($itinerary->group->type === 'CORE')
                                                                        全体
                                                                    @else
                                                                        {{ $itinerary->group->name }}
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- 背景の高さ調整 -->
                                <div style="height: {{ 100 + max($laneIndex * 80, 200) + ($untimedItineraries->count() > 0 ? 150 : 0) }}px;"></div>
                            </div>
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
    
    .timeline-container {
        overflow-x: auto;
    }
    
    .timeline-item:hover {
        z-index: 20;
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    .timeline-item {
        transition: all 0.2s ease;
    }
    
    /* スクロールバーのスタイル調整 */
    .timeline-container::-webkit-scrollbar {
        height: 8px;
    }
    
    .timeline-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    .timeline-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    .timeline-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const listViewBtn = document.getElementById('list-view-btn');
        const timelineViewBtn = document.getElementById('timeline-view-btn');
        const listView = document.getElementById('list-view');
        const timelineView = document.getElementById('timeline-view');
        
        // 初期表示モードをリストビューに設定
        let currentView = 'list';
        updateViewState();
        
        listViewBtn.addEventListener('click', function() {
            currentView = 'list';
            updateViewState();
        });
        
        timelineViewBtn.addEventListener('click', function() {
            currentView = 'timeline';
            updateViewState();
        });
        
        function updateViewState() {
            if (currentView === 'list') {
                listView.classList.remove('hidden');
                timelineView.classList.add('hidden');
                listViewBtn.classList.add('bg-blue-600', 'text-white');
                listViewBtn.classList.remove('text-gray-700', 'hover:text-gray-900');
                timelineViewBtn.classList.remove('bg-blue-600', 'text-white');
                timelineViewBtn.classList.add('text-gray-700', 'hover:text-gray-900');
            } else {
                listView.classList.add('hidden');
                timelineView.classList.remove('hidden');
                timelineViewBtn.classList.add('bg-blue-600', 'text-white');
                timelineViewBtn.classList.remove('text-gray-700', 'hover:text-gray-900');
                listViewBtn.classList.remove('bg-blue-600', 'text-white');
                listViewBtn.classList.add('text-gray-700', 'hover:text-gray-900');
            }
        }
    });
</script>
@endpush