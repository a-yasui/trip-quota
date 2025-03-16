<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('経費を編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('経費情報を編集') }}</h3>
                        <a href="{{ route('expenses.show', $expense) }}" class="btn-secondary">
                            {{ __('詳細に戻る') }}
                        </a>
                    </div>

                    <div class="vue-expense-form">
                        <expense-form
                            :expense='@json($expense)'
                            :travel-plan='@json($travelPlan)'
                            :branch-groups='@json($branchGroups)'
                            :members='@json($members)'
                            :selected-member-ids='@json(old('member_ids', $selectedMemberIds))'
                            form-action="{{ route('expenses.update', $expense) }}"
                            cancel-url="{{ route('expenses.show', $expense) }}"
                            :old-values='@json(old())'
                            :currencies='@json($currencies)'
                        >
                            <template v-slot:csrf>@csrf</template>
                            <template v-slot:method>@method('PUT')</template>

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
