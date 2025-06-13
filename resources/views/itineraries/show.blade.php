@extends('layouts.master')

@section('title', $itinerary->title . ' - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-5xl'])
        @component('components.page-header', ['title' => $itinerary->title, 'subtitle' => $travelPlan->plan_name])
            @slot('action')
                <div class="flex space-x-3">
                    <a href="{{ route('travel-plans.itineraries.edit', [$travelPlan->uuid, $itinerary->id]) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        編集
                    </a>
                    <form method="POST" action="{{ route('travel-plans.itineraries.destroy', [$travelPlan->uuid, $itinerary->id]) }}" 
                          onsubmit="return confirm('本当に削除しますか？')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            削除
                        </button>
                    </form>
                </div>
            @endslot
        @endcomponent

        @include('components.alerts')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- メイン情報 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 基本情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">タイトル</dt>
                                <dd class="mt-1 text-lg font-medium text-gray-900">{{ $itinerary->title }}</dd>
                            </div>
                            @if($itinerary->description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">説明</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $itinerary->description }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">日付</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->date->format('Y年m月d日（D）') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">時間</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($itinerary->start_time)
                                        {{ $itinerary->start_time->format('H:i') }}
                                        @if($itinerary->end_time)
                                            〜 {{ $itinerary->end_time->format('H:i') }}
                                        @endif
                                    @else
                                        時間未指定
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">対象グループ</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($itinerary->group)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $itinerary->group->group_type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            @if($itinerary->group->group_type === 'CORE')
                                                [全体] {{ $itinerary->group->name }}
                                            @else
                                                [班] {{ $itinerary->group->name }}
                                            @endif
                                        </span>
                                    @else
                                        すべてのメンバー
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">作成者</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->createdBy->name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- 交通手段情報 -->
                @if($itinerary->transportation_type || $itinerary->departure_location || $itinerary->arrival_location)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">交通手段・移動情報</h2>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                @if($itinerary->transportation_type)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">交通手段</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            @switch($itinerary->transportation_type)
                                                @case('walking')
                                                    🚶 徒歩
                                                    @break
                                                @case('bike')
                                                    🚲 自転車
                                                    @break
                                                @case('car')
                                                    🚗 車
                                                    @break
                                                @case('bus')
                                                    🚌 バス
                                                    @break
                                                @case('ferry')
                                                    ⛴️ フェリー
                                                    @break
                                                @case('airplane')
                                                    ✈️ 飛行機
                                                    @break
                                                @default
                                                    {{ $itinerary->transportation_type }}
                                            @endswitch
                                        </dd>
                                    </div>
                                @endif
                                @if($itinerary->airline)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">航空会社</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->airline }}</dd>
                                    </div>
                                @endif
                                @if($itinerary->flight_number)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">便名</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $itinerary->flight_number }}</dd>
                                    </div>
                                @endif
                                @if($itinerary->departure_location)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">出発地</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->departure_location }}</dd>
                                    </div>
                                @endif
                                @if($itinerary->arrival_location)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">到着地</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->arrival_location }}</dd>
                                    </div>
                                @endif
                                @if($itinerary->departure_time)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">出発時刻</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->departure_time->format('Y年m月d日 H:i') }}</dd>
                                    </div>
                                @endif
                                @if($itinerary->arrival_time)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">到着時刻</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $itinerary->arrival_time->format('Y年m月d日 H:i') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                <!-- 参加者 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">参加者 ({{ $itinerary->members->count() }}人)</h2>
                    </div>
                    <div class="px-6 py-4">
                        @if($itinerary->members->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($itinerary->members as $member)
                                    <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                        <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ substr($member->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">参加者が設定されていません</p>
                        @endif
                    </div>
                </div>

                <!-- メモ -->
                @if($itinerary->notes)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">メモ</h2>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $itinerary->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- サイドバー -->
            <div class="space-y-6">
                <!-- アクション -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">アクション</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <a href="{{ route('travel-plans.itineraries.edit', [$travelPlan->uuid, $itinerary->id]) }}" 
                           class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                            旅程を編集
                        </a>
                        <a href="{{ route('travel-plans.itineraries.create', $travelPlan->uuid) }}?date={{ $itinerary->date->format('Y-m-d') }}&group_id={{ $itinerary->group_id }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                            同じ条件で新規作成
                        </a>
                    </div>
                </div>

                <!-- 詳細情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">詳細情報</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">作成日</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $itinerary->created_at->format('Y/m/d H:i') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">最終更新</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $itinerary->updated_at->format('Y/m/d H:i') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">タイムゾーン</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $itinerary->timezone }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- 関連旅程 -->
                @if($relatedItineraries ?? false)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">同日の旅程</h3>
                        </div>
                        <div class="px-6 py-4">
                            <!-- 関連旅程のリスト表示 -->
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="{{ route('travel-plans.itineraries.index', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                ← 旅程一覧に戻る
            </a>
            <a href="{{ route('travel-plans.itineraries.timeline', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                タイムライン表示
            </a>
            <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" class="text-blue-600 hover:text-blue-800">
                旅行プラン詳細
            </a>
        </div>
    @endcomponent
@endsection