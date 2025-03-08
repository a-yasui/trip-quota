<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('旅程の追加') }} - {{ $travelPlan->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="vue-itinerary-form bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                             role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-4">
                        <a href="{{ route('travel-plans.itineraries.index', $travelPlan) }}"
                           class="text-blue-600 hover:text-blue-800">
                            &larr; {{ __('旅程一覧に戻る') }}
                        </a>
                    </div>

                    <itinerary-form
                        :transportation-types="{{ json_encode($transportationTypes) }}"
                        :timezones="{{ json_encode($timezones) }}"
                        :default-timezone="'{{ $defaultTimezone }}'"
                        :departure-date="'{{ $departureDate }}'"
                        :next-day="'{{ $nextDay }}'"
                        :branch-groups="{{ json_encode($branchGroups) }}"
                        :members="{{ json_encode($members) }}"
                        :selected-member-ids="{{ json_encode(old('member_ids', [])) }}"
                        :form-action="'{{ route('travel-plans.itineraries.store', $travelPlan) }}'"
                        :old-values="{{ json_encode(old()) }}"
                    >
                        <template v-slot:csrf>@csrf</template>

                        <template v-slot:transportation_type_error>
                            @error('transportation_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:departure_location_error>
                            @error('departure_location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:arrival_location_error>
                            @error('arrival_location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:departure_time_error>
                            @error('departure_time')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:departure_timezone_error>
                            @error('departure_timezone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:arrival_time_error>
                            @error('arrival_time')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:arrival_timezone_error>
                            @error('arrival_timezone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:company_name_error>
                            @error('company_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:reference_number_error>
                            @error('reference_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:notes_error>
                            @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:member_ids_error>
                            @error('member_ids')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </template>

                        <template v-slot:cancel_button>
                            <a href="{{ route('travel-plans.itineraries.index', $travelPlan) }}"
                               class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                                {{ __('キャンセル') }}
                            </a>
                        </template>

                        <template v-slot:submit_button>
                            <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('保存') }}
                            </button>
                        </template>
                    </itinerary-form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
