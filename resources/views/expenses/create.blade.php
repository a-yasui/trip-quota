@extends('layouts.master')

@section('title', '費用を追加 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => '費用を追加', 'subtitle' => $travelPlan->plan_name . 'に新しい費用を追加します。'])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.expenses.store', $travelPlan->uuid) }}" class="space-y-6 p-6">
                @csrf

                <!-- 費用タイトル -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        費用タイトル <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="ランチ代、交通費、お土産代など">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        説明・詳細
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                              placeholder="費用の詳細、場所、備考など">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 金額と通貨 -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label for="amount" class="block text-sm font-medium text-gray-700">
                            金額 <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               value="{{ old('amount') }}"
                               min="0.01"
                               step="0.01"
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="0.00">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">
                            通貨 <span class="text-red-500">*</span>
                        </label>
                        <select id="currency" 
                                name="currency"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="JPY" {{ old('currency', 'JPY') === 'JPY' ? 'selected' : '' }}>JPY (円)</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD (ドル)</option>
                            <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (ユーロ)</option>
                            <option value="KRW" {{ old('currency') === 'KRW' ? 'selected' : '' }}>KRW (ウォン)</option>
                            <option value="CNY" {{ old('currency') === 'CNY' ? 'selected' : '' }}>CNY (元)</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 費用日付 -->
                <div>
                    <label for="expense_date" class="block text-sm font-medium text-gray-700">
                        費用日付 <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="expense_date" 
                           name="expense_date" 
                           value="{{ old('expense_date', $travelPlan->departure_date->format('Y-m-d')) }}"
                           min="{{ $travelPlan->departure_date->format('Y-m-d') }}"
                           @if($travelPlan->return_date) max="{{ $travelPlan->return_date->format('Y-m-d') }}" @endif
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('expense_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- グループ選択 -->
                <div>
                    <label for="group_id" class="block text-sm font-medium text-gray-700">
                        対象グループ <span class="text-red-500">*</span>
                    </label>
                    <select id="group_id" 
                            name="group_id"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">グループを選択してください</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" 
                                {{ 
                                    old('group_id') 
                                        ? (old('group_id') == $group->id ? 'selected' : '') 
                                        : ($coreGroup && $group->id === $coreGroup->id ? 'selected' : '') 
                                }}>
                                {{ $group->name }} ({{ $group->members->count() }}人)
                            </option>
                        @endforeach
                    </select>
                    @error('group_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 支払い者 -->
                <div>
                    <label for="paid_by_member_id" class="block text-sm font-medium text-gray-700">
                        支払い者 <span class="text-red-500">*</span>
                    </label>
                    <select id="paid_by_member_id" 
                            name="paid_by_member_id"
                            required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">支払い者を選択してください</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" 
                                {{ 
                                    old('paid_by_member_id') 
                                        ? (old('paid_by_member_id') == $member->id ? 'selected' : '') 
                                        : ($currentUserMember && $member->id === $currentUserMember->id ? 'selected' : '') 
                                }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('paid_by_member_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 分割対象メンバー -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        分割対象メンバー
                    </label>
                    <div class="space-y-3">
                        @foreach($members as $member)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-md">
                                <div class="flex items-center">
                                    <input id="member_{{ $member->id }}" 
                                           name="member_assignments[{{ $loop->index }}][member_id]" 
                                           type="hidden" 
                                           value="{{ $member->id }}">
                                    
                                    <input id="participating_{{ $member->id }}" 
                                           name="member_assignments[{{ $loop->index }}][is_participating]" 
                                           type="checkbox" 
                                           value="1"
                                           checked
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    
                                    <label for="participating_{{ $member->id }}" class="ml-2 block text-sm text-gray-900">
                                        {{ $member->name }}
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <label for="amount_{{ $member->id }}" class="text-sm text-gray-500">個別金額:</label>
                                    <input type="number" 
                                           id="amount_{{ $member->id }}"
                                           name="member_assignments[{{ $loop->index }}][amount]"
                                           min="0"
                                           step="0.01"
                                           placeholder="自動計算"
                                           class="w-24 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        デフォルトでは全メンバーが等分で分割されます。個別金額を入力するとその金額が優先されます。
                    </p>
                    @error('member_assignments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 注意事項 -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">費用追加について</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>費用日付は旅行期間内である必要があります</li>
                                    <li>分割対象メンバーは後から変更できます</li>
                                    <li>個別金額を設定しない場合は自動で等分計算されます</li>
                                    <li>追加後、各メンバーの確認を経て費用が確定されます</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ナビゲーションリンク -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <a href="{{ route('travel-plans.expenses.index', $travelPlan->uuid) }}" 
                       class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        費用一覧に戻る
                    </a>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.expenses.index', $travelPlan->uuid) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        費用を追加
                    </button>
                </div>
            </form>
        </div>
    @endcomponent
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // グループ選択時にメンバー一覧を更新する処理（今後の拡張用）
        // 現在は全メンバーを表示しているが、将来的にはグループのメンバーのみ表示する可能性がある
    });
</script>
@endpush