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
                                @foreach(\App\Enums\TransportationType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('transportation_type') === $type->value ? 'selected' : '' }}>
                                        {{ $type->icon() }} {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('transportation_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 移動手段詳細（条件表示） -->
                        <div id="transportation_details">
                            <!-- 飛行機詳細 -->
                            <div id="airplane_details" class="transportation-detail" style="display: none;">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">飛行機詳細</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="airline" class="block text-sm font-medium text-gray-700">
                                            航空会社 <span class="text-red-500">*</span>
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
                                            便名 <span class="text-red-500">*</span>
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
                                    <div>
                                        <label for="departure_airport" class="block text-sm font-medium text-gray-700">
                                            出発空港
                                        </label>
                                        <input type="text" 
                                               id="departure_airport" 
                                               name="departure_airport" 
                                               value="{{ old('departure_airport') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：羽田空港">
                                        @error('departure_airport')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="arrival_airport" class="block text-sm font-medium text-gray-700">
                                            到着空港
                                        </label>
                                        <input type="text" 
                                               id="arrival_airport" 
                                               name="arrival_airport" 
                                               value="{{ old('arrival_airport') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：関西国際空港">
                                        @error('arrival_airport')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- 電車詳細 -->
                            <div id="train_details" class="transportation-detail" style="display: none;">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">電車詳細</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="train_line" class="block text-sm font-medium text-gray-700">
                                            路線名 <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               id="train_line" 
                                               name="train_line" 
                                               value="{{ old('train_line') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：東海道新幹線、JR山手線">
                                        @error('train_line')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="train_type" class="block text-sm font-medium text-gray-700">
                                            列車種別
                                        </label>
                                        <input type="text" 
                                               id="train_type" 
                                               name="train_type" 
                                               value="{{ old('train_type') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：のぞみ、ひかり、各駅停車">
                                        @error('train_type')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="departure_station" class="block text-sm font-medium text-gray-700">
                                            出発駅
                                        </label>
                                        <input type="text" 
                                               id="departure_station" 
                                               name="departure_station" 
                                               value="{{ old('departure_station') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：東京駅">
                                        @error('departure_station')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="arrival_station" class="block text-sm font-medium text-gray-700">
                                            到着駅
                                        </label>
                                        <input type="text" 
                                               id="arrival_station" 
                                               name="arrival_station" 
                                               value="{{ old('arrival_station') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：新大阪駅">
                                        @error('arrival_station')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- バス・フェリー詳細 -->
                            <div id="bus_ferry_details" class="transportation-detail" style="display: none;">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">バス・フェリー詳細</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="company" class="block text-sm font-medium text-gray-700">
                                            運営会社
                                        </label>
                                        <input type="text" 
                                               id="company" 
                                               name="company" 
                                               value="{{ old('company') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：JRバス、阪急フェリー">
                                        @error('company')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div></div>
                                    <div>
                                        <label for="departure_terminal" class="block text-sm font-medium text-gray-700">
                                            出発ターミナル・港
                                        </label>
                                        <input type="text" 
                                               id="departure_terminal" 
                                               name="departure_terminal" 
                                               value="{{ old('departure_terminal') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：東京駅八重洲口、新宿港">
                                        @error('departure_terminal')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="arrival_terminal" class="block text-sm font-medium text-gray-700">
                                            到着ターミナル・港
                                        </label>
                                        <input type="text" 
                                               id="arrival_terminal" 
                                               name="arrival_terminal" 
                                               value="{{ old('arrival_terminal') }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="例：大阪駅前、関西港">
                                        @error('arrival_terminal')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
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
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    参加者
                                </label>
                                <div class="flex space-x-2">
                                    <button type="button" id="select-all-members" 
                                            class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200">
                                        全選択
                                    </button>
                                    <button type="button" id="deselect-all-members" 
                                            class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded hover:bg-gray-200">
                                        全解除
                                    </button>
                                </div>
                            </div>
                            
                            <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                                <div id="member-selection-list" class="space-y-3">
                                    @foreach($members as $member)
                                        <div class="flex items-center member-item" data-groups="{{ $member->groups->pluck('id')->join(',') }}">
                                            <input id="member_{{ $member->id }}" 
                                                   name="member_ids[]" 
                                                   type="checkbox" 
                                                   value="{{ $member->id }}"
                                                   {{ in_array($member->id, old('member_ids', [])) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded member-checkbox">
                                            
                                            <div class="ml-3 flex items-center space-x-3 flex-1">
                                                <!-- アバター -->
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-medium">
                                                    {{ substr($member->name, 0, 1) }}
                                                </div>
                                                
                                                <div class="flex-1">
                                                    <label for="member_{{ $member->id }}" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                                        {{ $member->name }}
                                                    </label>
                                                    
                                                    <!-- グループ情報 -->
                                                    @if($member->groups->count() > 0)
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($member->groups as $group)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                                    {{ $group->type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                                    @if($group->type === 'CORE')
                                                                        全体
                                                                    @else
                                                                        {{ $group->name }}
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- 状態表示 -->
                                                <div class="flex items-center">
                                                    @if($member->is_confirmed)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
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
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mt-2 text-xs text-gray-500 space-y-1">
                                <p>選択しない場合、作成者のみが参加者として設定されます</p>
                                <p>グループを選択すると、そのグループのメンバーが自動的に選択されます</p>
                            </div>
                            
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
        const transportationDetails = document.querySelectorAll('.transportation-detail');
        const groupSelect = document.getElementById('group_id');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        const selectAllBtn = document.getElementById('select-all-members');
        const deselectAllBtn = document.getElementById('deselect-all-members');
        const memberItems = document.querySelectorAll('.member-item');

        // 移動手段詳細の表示切り替え
        function toggleTransportationDetails() {
            transportationDetails.forEach(detail => {
                detail.style.display = 'none';
            });

            document.querySelectorAll('#transportation_details input').forEach(input => {
                input.required = false;
            });

            const selectedType = transportationType.value;
            
            if (selectedType === '{{ \App\Enums\TransportationType::AIRPLANE->value }}') {
                document.getElementById('airplane_details').style.display = 'block';
                document.getElementById('airline').required = true;
                document.getElementById('flight_number').required = true;
            } else if (selectedType === '{{ \App\Enums\TransportationType::TRAIN->value }}') {
                document.getElementById('train_details').style.display = 'block';
                document.getElementById('train_line').required = true;
            } else if (selectedType === '{{ \App\Enums\TransportationType::BUS->value }}' || selectedType === '{{ \App\Enums\TransportationType::FERRY->value }}') {
                document.getElementById('bus_ferry_details').style.display = 'block';
            }
        }

        // グループ選択時のメンバー自動選択
        function handleGroupSelection() {
            const selectedGroupId = groupSelect.value;
            
            if (!selectedGroupId) {
                // グループが選択されていない場合、全メンバーを表示
                memberItems.forEach(item => {
                    item.style.display = 'flex';
                });
                return;
            }

            // グループのメンバーを自動選択
            memberItems.forEach(item => {
                const memberGroups = item.dataset.groups ? item.dataset.groups.split(',') : [];
                const checkbox = item.querySelector('.member-checkbox');
                
                if (memberGroups.includes(selectedGroupId)) {
                    item.style.display = 'flex';
                    if (!checkbox.checked) {
                        checkbox.checked = true;
                    }
                } else {
                    item.style.display = 'none';
                    checkbox.checked = false;
                }
            });
        }

        // 全選択/全解除機能
        function selectAllMembers() {
            const visibleCheckboxes = Array.from(memberCheckboxes).filter(cb => {
                return cb.closest('.member-item').style.display !== 'none';
            });
            visibleCheckboxes.forEach(cb => cb.checked = true);
        }

        function deselectAllMembers() {
            const visibleCheckboxes = Array.from(memberCheckboxes).filter(cb => {
                return cb.closest('.member-item').style.display !== 'none';
            });
            visibleCheckboxes.forEach(cb => cb.checked = false);
        }

        // イベントリスナーの設定
        transportationType.addEventListener('change', toggleTransportationDetails);
        groupSelect.addEventListener('change', handleGroupSelection);
        selectAllBtn.addEventListener('click', selectAllMembers);
        deselectAllBtn.addEventListener('click', deselectAllMembers);
        
        // 初期状態の設定
        toggleTransportationDetails();
        handleGroupSelection();
    });
</script>
@endpush