<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('経費を追加') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('経費情報を入力') }}</h3>
                        <a href="{{ route('travel-plans.show', $travelPlan) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('旅行計画に戻る') }}
                        </a>
                    </div>

                    <div class="vue-expense-form">
                        <expense-form
                            :travel-plan='@json($travelPlan)'
                            :branch-groups='@json($branchGroups)'
                            :members='@json($members)'
                            :selected-member-ids='@json(old('member_ids', []))'
                            form-action="{{ route('travel-plans.expenses.store', $travelPlan) }}"
                            cancel-url="{{ route('travel-plans.show', $travelPlan) }}"
                            :old-values='@json(old())'
                        >
                            @csrf

                            @error('description')
                                <template #description_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('amount')
                                <template #amount_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('currency')
                                <template #currency_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('expense_date')
                                <template #expense_date_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('category')
                                <template #category_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('payer_member_id')
                                <template #payer_member_id_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('notes')
                                <template #notes_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror

                            @error('member_ids')
                                <template #member_ids_error>
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                </template>
                            @enderror
                        </expense-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
