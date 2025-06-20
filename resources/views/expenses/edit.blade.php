@extends('layouts.master')

@section('title', '費用を編集 - ' . $expense->title)

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => '費用を編集', 'subtitle' => $expense->title . 'の情報を編集します。'])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.expenses.update', [$travelPlan->uuid, $expense->id]) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- 費用タイトル -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        費用タイトル <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title', $expense->title) }}"
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
                              placeholder="費用の詳細、場所、備考など">{{ old('description', $expense->description) }}</textarea>
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
                               value="{{ old('amount', $expense->amount) }}"
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
                            <option value="JPY" {{ old('currency', $expense->currency) === 'JPY' ? 'selected' : '' }}>JPY (円)</option>
                            <option value="USD" {{ old('currency', $expense->currency) === 'USD' ? 'selected' : '' }}>USD (ドル)</option>
                            <option value="EUR" {{ old('currency', $expense->currency) === 'EUR' ? 'selected' : '' }}>EUR (ユーロ)</option>
                            <option value="KRW" {{ old('currency', $expense->currency) === 'KRW' ? 'selected' : '' }}>KRW (ウォン)</option>
                            <option value="CNY" {{ old('currency', $expense->currency) === 'CNY' ? 'selected' : '' }}>CNY (元)</option>
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
                           value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
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
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('group_id', $expense->group_id) == $group->id ? 'selected' : '' }}>
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
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('paid_by_member_id', $expense->paid_by_member_id) == $member->id ? 'selected' : '' }}>
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
                            @php
                                $memberExpense = $expense->members->where('id', $member->id)->first();
                                $isParticipating = $memberExpense ? $memberExpense->pivot->is_participating : false;
                                $customAmount = $memberExpense ? $memberExpense->pivot->amount : null;
                                $oldParticipating = old("member_assignments.{$loop->index}.is_participating", $isParticipating);
                                $oldAmount = old("member_assignments.{$loop->index}.amount", $customAmount);
                            @endphp
                            
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
                                           {{ $oldParticipating ? 'checked' : '' }}
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
                                           value="{{ $oldAmount }}"
                                           min="0"
                                           step="0.01"
                                           placeholder="自動計算"
                                           class="w-24 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        デフォルトでは参加メンバーが等分で分割されます。個別金額を入力するとその金額が優先されます。
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
                            <h3 class="text-sm font-medium text-blue-800">費用編集について</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>費用日付は旅行期間内である必要があります</li>
                                    <li>メンバーの参加状況を変更すると最新の状態に更新されます</li>
                                    <li>金額や分割内容の変更ができます</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.expenses.show', [$travelPlan->uuid, $expense->id]) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        変更を保存
                    </button>
                </div>
            </form>
        </div>
    @endcomponent
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        const memberCheckboxes = document.querySelectorAll('input[name$="[is_participating]"]');
        const memberAmountInputs = document.querySelectorAll('input[name$="[amount]"]');
        
        function calculateSplitAmounts() {
            const totalAmount = parseFloat(amountInput.value) || 0;
            const participatingMembers = [];
            
            // 参加メンバーとカスタム金額を収集
            memberCheckboxes.forEach((checkbox, index) => {
                if (checkbox.checked) {
                    const amountInput = memberAmountInputs[index];
                    const customAmount = parseFloat(amountInput.value) || null;
                    participatingMembers.push({
                        checkbox: checkbox,
                        amountInput: amountInput,
                        customAmount: customAmount
                    });
                }
            });
            
            if (participatingMembers.length === 0) {
                return;
            }
            
            // カスタム金額設定済みメンバーとそれ以外を分離
            let customAmountTotal = 0;
            let membersWithCustomAmount = [];
            let membersWithoutCustomAmount = [];
            
            participatingMembers.forEach(member => {
                if (member.customAmount !== null && member.customAmount > 0) {
                    customAmountTotal += member.customAmount;
                    membersWithCustomAmount.push(member);
                } else {
                    membersWithoutCustomAmount.push(member);
                }
            });
            
            // 残り金額を計算
            const remainingAmount = totalAmount - customAmountTotal;
            const remainingMemberCount = membersWithoutCustomAmount.length;
            
            // エラーチェックと表示
            const errorMessage = document.getElementById('split-error-message');
            if (!errorMessage) {
                // エラーメッセージ要素を作成
                const errorDiv = document.createElement('div');
                errorDiv.id = 'split-error-message';
                errorDiv.className = 'mt-2 text-sm text-red-600 hidden';
                amountInput.parentNode.appendChild(errorDiv);
            }
            
            const errorDiv = document.getElementById('split-error-message');
            
            if (remainingAmount < 0) {
                errorDiv.textContent = 'カスタム金額の合計が総金額を超えています。';
                errorDiv.classList.remove('hidden');
                return;
            } else {
                errorDiv.classList.add('hidden');
            }
            
            // 残りメンバーの一人当たり金額を計算して表示
            const remainingSplitAmount = remainingMemberCount > 0 ? remainingAmount / remainingMemberCount : 0;
            
            membersWithoutCustomAmount.forEach(member => {
                member.amountInput.placeholder = `自動計算: ${remainingSplitAmount.toFixed(0)}`;
            });
            
            // 合計金額表示を更新
            updateSummary(totalAmount, participatingMembers.length, customAmountTotal, remainingAmount);
        }
        
        function updateSummary(totalAmount, participatingCount, customAmountTotal, remainingAmount) {
            // サマリー要素がない場合は作成
            let summaryDiv = document.getElementById('split-summary');
            if (!summaryDiv) {
                summaryDiv = document.createElement('div');
                summaryDiv.id = 'split-summary';
                summaryDiv.className = 'mt-4 p-3 bg-gray-50 rounded-md text-sm';
                
                // メンバー割り当てセクションの後に挿入
                const memberSection = document.querySelector('.space-y-3').parentNode;
                memberSection.appendChild(summaryDiv);
            }
            
            summaryDiv.innerHTML = `
                <div class="grid grid-cols-2 gap-2">
                    <div><span class="text-gray-600">総金額:</span> <span class="font-semibold">${totalAmount.toLocaleString()}</span></div>
                    <div><span class="text-gray-600">参加者数:</span> <span class="font-semibold">${participatingCount}人</span></div>
                    <div><span class="text-gray-600">設定済み合計:</span> <span class="font-semibold">${customAmountTotal.toLocaleString()}</span></div>
                    <div><span class="text-gray-600">残り金額:</span> <span class="font-semibold">${remainingAmount.toLocaleString()}</span></div>
                </div>
            `;
        }
        
        // イベントリスナーを設定
        amountInput.addEventListener('input', calculateSplitAmounts);
        memberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', calculateSplitAmounts);
        });
        memberAmountInputs.forEach(input => {
            input.addEventListener('input', calculateSplitAmounts);
        });
        
        // 初期計算
        calculateSplitAmounts();
    });
</script>
@endpush