<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('旅程の編集') }} - {{ $travelPlan->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-4">
                        <a href="{{ route('travel-plans.itineraries.index', $travelPlan) }}" class="text-blue-600 hover:text-blue-800">
                            &larr; {{ __('旅程一覧に戻る') }}
                        </a>
                    </div>

                    <form method="POST" action="{{ route('travel-plans.itineraries.update', [$travelPlan, $itinerary]) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 交通手段 -->
                            <div>
                                <label for="transportation_type" class="block text-sm font-medium text-gray-700">{{ __('交通手段') }} <span class="text-red-500">*</span></label>
                                <select id="transportation_type" name="transportation_type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">{{ __('選択してください') }}</option>
                                    @foreach($transportationTypes as $type)
                                        <option value="{{ $type->value }}" {{ old('transportation_type', $itinerary->transportation_type->value) == $type->value ? 'selected' : '' }}>
                                            @switch($type->value)
                                                @case('flight')
                                                    {{ __('飛行機') }}
                                                    @break
                                                @case('train')
                                                    {{ __('電車') }}
                                                    @break
                                                @case('bus')
                                                    {{ __('バス') }}
                                                    @break
                                                @case('ferry')
                                                    {{ __('フェリー') }}
                                                    @break
                                                @case('car')
                                                    {{ __('車') }}
                                                    @break
                                                @case('walk')
                                                    {{ __('徒歩') }}
                                                    @break
                                                @case('bike')
                                                    {{ __('バイク') }}
                                                    @break
                                                @default
                                                    {{ __('その他') }}
                                            @endswitch
                                        </option>
                                    @endforeach
                                </select>
                                @error('transportation_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 出発地 -->
                            <div>
                                <label for="departure_location" class="block text-sm font-medium text-gray-700">{{ __('出発地') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="departure_location" id="departure_location" value="{{ old('departure_location', $itinerary->departure_location) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                @error('departure_location')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 到着地 -->
                            <div>
                                <label for="arrival_location" class="block text-sm font-medium text-gray-700">{{ __('到着地') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="arrival_location" id="arrival_location" value="{{ old('arrival_location', $itinerary->arrival_location) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                @error('arrival_location')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 出発時刻 -->
                            <div>
                                <label for="departure_time" class="block text-sm font-medium text-gray-700">{{ __('出発時刻') }} <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="departure_time" id="departure_time" value="{{ old('departure_time', $itinerary->departure_time->format('Y-m-d\TH:i')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                @error('departure_time')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 到着時刻 -->
                            <div>
                                <label for="arrival_time" class="block text-sm font-medium text-gray-700">{{ __('到着時刻') }} <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="arrival_time" id="arrival_time" value="{{ old('arrival_time', $itinerary->arrival_time->format('Y-m-d\TH:i')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                @error('arrival_time')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 会社名 -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">{{ __('会社名') }} <span class="text-red-500 flight-required {{ $itinerary->transportation_type->value === 'flight' ? '' : 'hidden' }}">*</span></label>
                                <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $itinerary->company_name) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $itinerary->transportation_type->value === 'flight' ? 'required' : '' }}>
                                <p class="mt-1 text-sm text-gray-500 flight-note {{ $itinerary->transportation_type->value === 'flight' ? '' : 'hidden' }}">{{ __('飛行機の場合は必須です') }}</p>
                                @error('company_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 便名・列車番号など -->
                            <div>
                                <label for="reference_number" class="block text-sm font-medium text-gray-700">{{ __('便名・列車番号など') }} <span class="text-red-500 flight-required {{ $itinerary->transportation_type->value === 'flight' ? '' : 'hidden' }}">*</span></label>
                                <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number', $itinerary->reference_number) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" {{ $itinerary->transportation_type->value === 'flight' ? 'required' : '' }}>
                                <p class="mt-1 text-sm text-gray-500 flight-note {{ $itinerary->transportation_type->value === 'flight' ? '' : 'hidden' }}">{{ __('飛行機の場合は必須です') }}</p>
                                @error('reference_number')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- メモ -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('メモ') }}</label>
                                <textarea name="notes" id="notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $itinerary->notes) }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 参加メンバー -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('参加メンバー') }}</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($members as $member)
                                        <div class="flex items-center">
                                            <input type="checkbox" id="member_{{ $member->id }}" name="member_ids[]" value="{{ $member->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ in_array($member->id, old('member_ids', $selectedMemberIds)) ? 'checked' : '' }}>
                                            <label for="member_{{ $member->id }}" class="ml-2 block text-sm text-gray-900">
                                                {{ $member->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('member_ids')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('travel-plans.itineraries.index', $travelPlan) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                                {{ __('キャンセル') }}
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('更新') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transportationTypeSelect = document.getElementById('transportation_type');
            const flightRequiredElements = document.querySelectorAll('.flight-required');
            const flightNoteElements = document.querySelectorAll('.flight-note');
            
            function updateFlightFields() {
                const isFlightSelected = transportationTypeSelect.value === 'flight';
                
                flightRequiredElements.forEach(element => {
                    if (isFlightSelected) {
                        element.classList.remove('hidden');
                    } else {
                        element.classList.add('hidden');
                    }
                });
                
                flightNoteElements.forEach(element => {
                    if (isFlightSelected) {
                        element.classList.remove('hidden');
                    } else {
                        element.classList.add('hidden');
                    }
                });
                
                const companyNameInput = document.getElementById('company_name');
                const referenceNumberInput = document.getElementById('reference_number');
                
                if (isFlightSelected) {
                    companyNameInput.setAttribute('required', 'required');
                    referenceNumberInput.setAttribute('required', 'required');
                } else {
                    companyNameInput.removeAttribute('required');
                    referenceNumberInput.removeAttribute('required');
                }
            }
            
            transportationTypeSelect.addEventListener('change', updateFlightFields);
            
            // 初期表示時にも実行
            updateFlightFields();
        });
    </script>
</x-app-layout>
