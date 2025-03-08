<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('経費詳細') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('経費詳細') }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('travel-plans.show', $expense->travelPlan) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('旅行計画に戻る') }}
                            </a>
                            <a href="{{ route('expenses.edit', $expense) }}" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 focus:bg-lime-400 active:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('編集') }}
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $expense->description }}</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                {{ $expense->expense_date->format('Y年m月d日') }}
                            </p>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('金額') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ number_format($expense->amount) }} {{ $expense->currency }}
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('支払者') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $expense->payerMember->name }}
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('カテゴリ') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        @php
                                            $categoryLabels = [
                                                'food' => '食事',
                                                'transportation' => '交通',
                                                'accommodation' => '宿泊',
                                                'entertainment' => '娯楽',
                                                'shopping' => '買い物',
                                                'other' => 'その他',
                                            ];
                                        @endphp
                                        {{ $expense->category ? ($categoryLabels[$expense->category] ?? $expense->category) : '-' }}
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('状態') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        @if($expense->is_settled)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('精算済み') }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ __('未精算') }}
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('メモ') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $expense->notes ?? '-' }}
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('参加メンバー') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                            @foreach($expense->members as $member)
                                                <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                                    <div class="w-0 flex-1 flex items-center">
                                                        <span class="ml-2 flex-1 w-0 truncate">
                                                            {{ $member->name }}
                                                            @if($member->id == $expense->payer_member_id)
                                                                <span class="ml-2 text-xs text-gray-500">{{ __('支払者') }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                                        <!-- 精算済みボタン -->
                                                        <button 
                                                            type="button"
                                                            class="px-3 py-1 text-xs font-semibold rounded-md transition-colors duration-200 ease-in-out {{ $member->pivot->is_paid ? 'bg-green-500 text-white hover:bg-green-600' : 'bg-red-500 text-white hover:bg-red-600' }}"
                                                            onclick="togglePaymentStatus({{ $expense->id }}, {{ $member->id }})"
                                                            data-member-id="{{ $member->id }}"
                                                            data-expense-id="{{ $expense->id }}"
                                                            data-is-paid="{{ $member->pivot->is_paid ? 'true' : 'false' }}"
                                                        >
                                                            {{ $member->pivot->is_paid ? __('精算済み') : __('未精算') }}
                                                        </button>
                                                        
                                                        <!-- 金額（右揃え） -->
                                                        <span class="font-medium text-right w-24">
                                                            {{ number_format($member->pivot->share_amount) }} {{ $expense->currency }}
                                                        </span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('この経費を削除してもよろしいですか？') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('削除') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    function togglePaymentStatus(expenseId, memberId) {
        // CSRFトークンを取得
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // ボタン要素を取得
        const button = document.querySelector(`button[data-member-id="${memberId}"][data-expense-id="${expenseId}"]`);
        
        // 現在の状態を取得
        const isPaid = button.getAttribute('data-is-paid') === 'true';
        
        // APIリクエストを送信
        fetch(`/expenses/${expenseId}/members/${memberId}/toggle-payment`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ボタンの状態を更新
                button.setAttribute('data-is-paid', data.is_paid ? 'true' : 'false');
                
                // ボタンのテキストを更新
                button.textContent = data.is_paid ? '精算済み' : '未精算';
                
                // ボタンのスタイルを更新
                if (data.is_paid) {
                    button.classList.remove('bg-red-500', 'hover:bg-red-600');
                    button.classList.add('bg-green-500', 'hover:bg-green-600');
                } else {
                    button.classList.remove('bg-green-500', 'hover:bg-green-600');
                    button.classList.add('bg-red-500', 'hover:bg-red-600');
                }
                
                // 経費全体の状態表示も更新
                const statusElement = document.querySelector('.bg-white.px-4.py-5.sm\\:grid.sm\\:grid-cols-3.sm\\:gap-4.sm\\:px-6 .text-xs.leading-5.font-semibold.rounded-full');
                if (statusElement) {
                    if (data.is_settled) {
                        statusElement.textContent = '精算済み';
                        statusElement.classList.remove('bg-yellow-100', 'text-yellow-800');
                        statusElement.classList.add('bg-green-100', 'text-green-800');
                    } else {
                        statusElement.textContent = '未精算';
                        statusElement.classList.remove('bg-green-100', 'text-green-800');
                        statusElement.classList.add('bg-yellow-100', 'text-yellow-800');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('更新に失敗しました。');
        });
    }
</script>
@endpush

</x-app-layout>
