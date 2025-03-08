<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('旅程詳細') }} - {{ $travelPlan->title }}
            </h2>
            <div>
                <a href="{{ route('travel-plans.itineraries.edit', [$travelPlan, $itinerary]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition mr-2">
                    {{ __('編集') }}
                </a>
                <form class="inline-block" action="{{ route('travel-plans.itineraries.destroy', [$travelPlan, $itinerary]) }}" method="POST" onsubmit="return confirm('{{ __('この旅程を削除してもよろしいですか？') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
                        {{ __('削除') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <a href="{{ route('travel-plans.itineraries.index', $travelPlan) }}" class="text-blue-600 hover:text-blue-800">
                            &larr; {{ __('旅程一覧に戻る') }}
                        </a>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 交通手段 -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('交通手段') }}</h3>
                                <div class="text-gray-700">
                                    @switch($itinerary->transportation_type->value)
                                        @case('flight')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11.43a1 1 0 00-.725-.962l-5-1.429a1 1 0 01.725-1.962l5 1.429a1 1 0 00.725-.038l5-1.429a1 1 0 011.169 1.409l-7 14z" />
                                                </svg>
                                                {{ __('飛行機') }}
                                            </span>
                                            @break
                                        @case('train')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ __('電車') }}
                                            </span>
                                            @break
                                        @case('bus')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H11a2.5 2.5 0 014.9 0H17a1 1 0 001-1V5a1 1 0 00-1-1H3zm0 2h13v8H3V6z" />
                                                </svg>
                                                {{ __('バス') }}
                                            </span>
                                            @break
                                        @case('ferry')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H11a2.5 2.5 0 014.9 0H17a1 1 0 001-1V5a1 1 0 00-1-1H3zm0 2h13v8H3V6z" />
                                                </svg>
                                                {{ __('フェリー') }}
                                            </span>
                                            @break
                                        @case('car')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H11a2.5 2.5 0 014.9 0H17a1 1 0 001-1V5a1 1 0 00-1-1H3zm0 2h13v8H3V6z" />
                                                </svg>
                                                {{ __('車') }}
                                            </span>
                                            @break
                                        @case('walk')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                </svg>
                                                {{ __('徒歩') }}
                                            </span>
                                            @break
                                        @case('bike')
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                </svg>
                                                {{ __('バイク') }}
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                </svg>
                                                {{ __('その他') }}
                                            </span>
                                    @endswitch
                                </div>
                            </div>

                            <!-- 出発地・到着地 -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('出発地・到着地') }}</h3>
                                <div class="flex items-center text-gray-700">
                                    <div class="font-medium">{{ $itinerary->departure_location }}</div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                    <div class="font-medium">{{ $itinerary->arrival_location }}</div>
                                </div>
                            </div>

                            <!-- 出発時刻・到着時刻 -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('出発時刻・到着時刻') }}</h3>
                                <div class="text-gray-700">
                                    <div class="mb-1">
                                        <span class="font-medium">{{ __('出発') }}:</span> {{ $itinerary->departure_time->format('Y年m月d日 H:i') }}
                                    </div>
                                    <div>
                                        <span class="font-medium">{{ __('到着') }}:</span> {{ $itinerary->arrival_time->format('Y年m月d日 H:i') }}
                                    </div>
                                </div>
                            </div>

                            <!-- 会社名・便名 -->
                            @if($itinerary->company_name || $itinerary->reference_number)
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('会社名・便名') }}</h3>
                                    <div class="text-gray-700">
                                        @if($itinerary->company_name)
                                            <div class="mb-1">
                                                <span class="font-medium">{{ __('会社名') }}:</span> {{ $itinerary->company_name }}
                                            </div>
                                        @endif
                                        @if($itinerary->reference_number)
                                            <div>
                                                <span class="font-medium">{{ __('便名・番号') }}:</span> {{ $itinerary->reference_number }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- メモ -->
                            @if($itinerary->notes)
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('メモ') }}</h3>
                                    <div class="text-gray-700 whitespace-pre-line">{{ $itinerary->notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 参加メンバー -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('参加メンバー') }}</h3>
                        @if($itinerary->members->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($itinerary->members as $member)
                                    <div class="bg-white p-4 rounded-md shadow-sm">
                                        <div class="font-medium text-gray-900">{{ $member->name }}</div>
                                        @if($member->email)
                                            <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">{{ __('参加メンバーはいません。') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
