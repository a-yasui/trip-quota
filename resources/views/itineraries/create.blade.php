@extends('layouts.master')

@section('title', '旅程作成 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => '旅程作成', 'subtitle' => $travelPlan->plan_name . 'の新しい旅程を作成します。'])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.itineraries.store', $travelPlan->uuid) }}" class="space-y-6 p-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- 左側：基本情報 -->
                    <div class="space-y-6">
                        <!-- タイトル -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                タイトル <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="例：東京駅集合、チェックイン、観光スポット訪問">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 説明 -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                説明
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="詳細な内容、持参物、注意事項などを記載してください...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 日付と時刻 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">
                                    日付 <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       id="date" 
                                       name="date" 
                                       value="{{ old('date', $defaultDate) }}"
                                       min="{{ $travelPlan->departure_date->format('Y-m-d') }}"
                                       @if($travelPlan->return_date) max="{{ $travelPlan->return_date->format('Y-m-d') }}" @endif
                                       required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">
                                    出発時刻
                                </label>
                                <input type="time" 
                                       id="start_time" 
                                       name="start_time" 
                                       value="{{ old('start_time') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">
                                    到着時刻
                                </label>
                                <input type="time" 
                                       id="end_time" 
                                       name="end_time" 
                                       value="{{ old('end_time') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- グループ選択 -->
                        <div>
                            <label for="group_id" class="block text-sm font-medium text-gray-700">
                                対象グループ
                            </label>
                            <select name="group_id" id="group_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">すべてのメンバー</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id', $defaultGroupId) == $group->id ? 'selected' : '' }}>
                                        @if($group->group_type === 'CORE')
                                            [全体] {{ $group->name }}
                                        @else
                                            [班] {{ $group->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- 右側：交通手段・参加者 -->
                    <div class="space-y-6">
                        <!-- 交通手段 -->
                        <div>
                            <label for="transportation_type" class="block text-sm font-medium text-gray-700">
                                交通手段
                            </label>
                            <select name="transportation_type" id="transportation_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">選択してください</option>
                                <option value="walking" {{ old('transportation_type') === 'walking' ? 'selected' : '' }}>徒歩</option>
                                <option value="bike" {{ old('transportation_type') === 'bike' ? 'selected' : '' }}>自転車</option>
                                <option value="car" {{ old('transportation_type') === 'car' ? 'selected' : '' }}>車</option>
                                <option value="bus" {{ old('transportation_type') === 'bus' ? 'selected' : '' }}>バス</option>
                                <option value="train" {{ old('transportation_type') === 'train' ? 'selected' : '' }}>電車</option>
                                <option value="ferry" {{ old('transportation_type') === 'ferry' ? 'selected' : '' }}>フェリー</option>
                                <option value="airplane" {{ old('transportation_type') === 'airplane' ? 'selected' : '' }}>飛行機</option>
                            </select>
                            @error('transportation_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 飛行機詳細（条件表示） -->
                        <div id="airplane_details" style="display: none;">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="airline" class="block text-sm font-medium text-gray-700">
                                        航空会社
                                    </label>
                                    <input type="text" 
                                           id="airline" 
                                           name="airline" 
                                           value="{{ old('airline') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           placeholder="例：JAL、ANA">
                                    @error('airline')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="flight_number" class="block text-sm font-medium text-gray-700">
                                        便名
                                    </label>
                                    <input type="text" 
                                           id="flight_number" 
                                           name="flight_number" 
                                           value="{{ old('flight_number') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           placeholder="例：JL123">
                                    @error('flight_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 出発地・到着地 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="departure_location" class="block text-sm font-medium text-gray-700">
                                    出発地
                                </label>
                                <input type="text" 
                                       id="departure_location" 
                                       name="departure_location" 
                                       value="{{ old('departure_location') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="例：東京駅、ホテル">
                                @error('departure_location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="arrival_location" class="block text-sm font-medium text-gray-700">
                                    到着地
                                </label>
                                <input type="text" 
                                       id="arrival_location" 
                                       name="arrival_location" 
                                       value="{{ old('arrival_location') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="例：観光地、空港">
                                @error('arrival_location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- 参加者選択 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                参加者
                            </label>
                            <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                @foreach($members as $member)
                                    <div class="flex items-center">
                                        <input id="member_{{ $member->id }}" 
                                               name="member_ids[]" 
                                               type="checkbox" 
                                               value="{{ $member->id }}"
                                               {{ in_array($member->id, old('member_ids', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="member_{{ $member->id }}" class="ml-3 text-sm text-gray-900">
                                            {{ $member->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                選択しない場合、作成者のみが参加者として設定されます
                            </p>
                            @error('member_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- メモ -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                メモ
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="その他のメモや特記事項...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.itineraries.index', $travelPlan->uuid) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        旅程を作成
                    </button>
                </div>
            </form>
        </div>
    @endcomponent
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const transportationType = document.getElementById('transportation_type');
        const airplaneDetails = document.getElementById('airplane_details');

        function toggleAirplaneDetails() {
            if (transportationType.value === 'airplane') {
                airplaneDetails.style.display = 'block';
                document.getElementById('flight_number').required = true;
            } else {
                airplaneDetails.style.display = 'none';
                document.getElementById('flight_number').required = false;
            }
        }

        transportationType.addEventListener('change', toggleAirplaneDetails);
        
        // 初期状態の設定
        toggleAirplaneDetails();
    });
</script>
@endpush