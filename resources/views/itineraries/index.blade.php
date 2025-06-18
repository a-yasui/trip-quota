@extends('layouts.master')

@section('title', '旅程管理 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container')
        @component('components.page-header', ['title' => '旅程管理', 'subtitle' => $travelPlan->plan_name])
            @slot('action')
                <div class="flex space-x-3">
                    <a href="{{ route('travel-plans.itineraries.timeline', $travelPlan->uuid) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        タイムライン表示
                    </a>
                    <a href="{{ route('travel-plans.itineraries.create', $travelPlan->uuid) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        旅程を追加
                    </a>
                </div>
            @endslot
        @endcomponent

        @include('components.alerts')

        <!-- フィルター -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <label for="group_id" class="block text-sm font-medium text-gray-700 mb-1">グループでフィルター</label>
                        <select name="group_id" id="group_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">すべてのグループ</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                    @if($group->group_type === 'CORE')
                                        [全体] {{ $group->name }}
                                    @else
                                        [班] {{ $group->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">日付でフィルター</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}" 
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex space-x-2 pt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            フィルター適用
                        </button>
                        <a href="{{ route('travel-plans.itineraries.index', $travelPlan->uuid) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            クリア
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 旅程一覧 -->
        @if($itineraries->count() > 0)
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">旅程一覧 ({{ $itineraries->count() }}件)</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($itineraries as $itinerary)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            @if($itinerary->transportation_type)
                                                <span class="text-blue-600 text-xl">{{ $itinerary->transportation_icon }}</span>
                                            @else
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $itinerary->title }}</h3>
                                            <div class="mt-1 flex items-center text-sm text-gray-500 space-x-4">
                                                <span class="flex items-center">
                                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ $itinerary->date->format('Y年m月d日（D）') }}
                                                </span>
                                                @if($itinerary->start_time)
                                                    <span class="flex items-center">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        {{ $itinerary->start_time->format('H:i') }}
                                                        @if($itinerary->end_time)
                                                            〜{{ $itinerary->end_time->format('H:i') }}
                                                        @endif
                                                    </span>
                                                @endif
                                                @if($itinerary->group)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $itinerary->group->group_type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ $itinerary->group->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($itinerary->description)
                                                <p class="mt-2 text-sm text-gray-700">{{ Str::limit($itinerary->description, 100) }}</p>
                                            @endif
                                            @if($itinerary->members->count() > 0)
                                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                                    <svg class="flex-shrink-0 mr-1.5 h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    </svg>
                                                    参加者: {{ $itinerary->members->pluck('name')->join(', ') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 ml-4">
                                    <a href="{{ route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id]) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        詳細
                                    </a>
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
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">旅程がありません</h3>
                <p class="mt-1 text-sm text-gray-500">最初の旅程を作成して、旅行スケジュールを管理しましょう。</p>
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