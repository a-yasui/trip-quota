@extends('layouts.master')

@section('title', 'メンバー追加 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-3xl'])
        @component('components.page-header', ['title' => 'メンバー追加', 'subtitle' => $travelPlan->plan_name . 'にメンバーを追加します。'])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.members.store', $travelPlan->uuid) }}" class="space-y-6 p-6">
                @csrf

                <!-- メンバー追加方法選択 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        追加方法 <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="member_type_name_only" 
                                   name="member_type" 
                                   type="radio" 
                                   value="name_only"
                                   {{ old('member_type', 'name_only') === 'name_only' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="member_type_name_only" class="ml-2 block text-sm text-gray-900">
                                表示名のみで追加（後で関連付け可能）
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="member_type_with_invitation" 
                                   name="member_type" 
                                   type="radio" 
                                   value="with_invitation"
                                   {{ old('member_type') === 'with_invitation' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="member_type_with_invitation" class="ml-2 block text-sm text-gray-900">
                                招待付きで追加
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 表示名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        表示名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="メンバー一覧で表示される名前"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 招待セクション（招待付きの場合のみ表示） -->
                <div id="invitation_section" style="display: none;">
                    <!-- 招待方法選択 -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            招待方法
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input id="invitation_type_email" 
                                       name="invitation_type" 
                                       type="radio" 
                                       value="email"
                                       {{ old('invitation_type', 'email') === 'email' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="invitation_type_email" class="ml-2 block text-sm text-gray-900">
                                    メールアドレスで招待
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="invitation_type_account" 
                                       name="invitation_type" 
                                       type="radio" 
                                       value="account"
                                       {{ old('invitation_type') === 'account' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="invitation_type_account" class="ml-2 block text-sm text-gray-900">
                                    アカウント名で招待
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- メールアドレス入力 -->
                    <div id="email_section">
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            メールアドレス <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="example@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- アカウント名入力 -->
                    <div id="account_section" style="display: none;">
                        <label for="account_name" class="block text-sm font-medium text-gray-700">
                            アカウント名 <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                @
                            </span>
                            <input type="text" 
                                   id="account_name" 
                                   name="account_name" 
                                   value="{{ old('account_name') }}"
                                   class="flex-1 block w-full min-w-0 rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="username">
                        </div>
                        @error('account_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 注意事項 -->
                <div id="info_section">
                    <div id="name_only_info" class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">表示名のみでの追加について</h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>メンバーは即座に追加され、すぐに費用や旅程に参加させることができます</li>
                                        <li>後からメンバー詳細画面で既存ユーザーとの関連付けが可能です</li>
                                        <li>関連付けには対象ユーザーの承認が必要です</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="invitation_info" class="bg-blue-50 border border-blue-200 rounded-md p-4" style="display: none;">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">招待について</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>招待されたユーザーには招待リンクが送信されます</li>
                                        <li>招待の有効期限は7日間です</li>
                                        <li>アカウント名で招待する場合、既存のTripQuotaユーザーが対象です</li>
                                        <li>同じメールアドレス・アカウントは重複して招待できません</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.members.index', $travelPlan->uuid) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            id="submit_button"
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        メンバーを追加
                    </button>
                </div>
            </form>
        </div>
    @endcomponent
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameOnlyRadio = document.getElementById('member_type_name_only');
        const withInvitationRadio = document.getElementById('member_type_with_invitation');
        const invitationSection = document.getElementById('invitation_section');
        const nameOnlyInfo = document.getElementById('name_only_info');
        const invitationInfo = document.getElementById('invitation_info');
        const submitButton = document.getElementById('submit_button');

        const emailRadio = document.getElementById('invitation_type_email');
        const accountRadio = document.getElementById('invitation_type_account');
        const emailSection = document.getElementById('email_section');
        const accountSection = document.getElementById('account_section');

        function toggleMemberType() {
            if (nameOnlyRadio.checked) {
                // 表示名のみの場合
                invitationSection.style.display = 'none';
                nameOnlyInfo.style.display = 'block';
                invitationInfo.style.display = 'none';
                submitButton.textContent = 'メンバーを追加';
                
                // 招待関連フィールドの必須項目をクリア
                document.getElementById('email').required = false;
                document.getElementById('account_name').required = false;
                // 招待タイプのラジオボタンの選択を解除
                if (emailRadio) emailRadio.checked = false;
                if (accountRadio) accountRadio.checked = false;
            } else {
                // 招待付きの場合
                invitationSection.style.display = 'block';
                nameOnlyInfo.style.display = 'none';
                invitationInfo.style.display = 'block';
                submitButton.textContent = '招待を送信';
                
                // 招待方法に応じて必須項目を設定
                toggleInvitationType();
            }
        }

        function toggleInvitationType() {
            if (!withInvitationRadio.checked) return;
            
            if (emailRadio.checked) {
                emailSection.style.display = 'block';
                accountSection.style.display = 'none';
                document.getElementById('email').required = true;
                document.getElementById('account_name').required = false;
                // 非表示フィールドの値をクリア
                document.getElementById('account_name').value = '';
            } else {
                emailSection.style.display = 'none';
                accountSection.style.display = 'block';
                document.getElementById('email').required = false;
                document.getElementById('account_name').required = true;
                // 非表示フィールドの値をクリア
                document.getElementById('email').value = '';
            }
        }

        // イベントリスナー設定
        nameOnlyRadio.addEventListener('change', toggleMemberType);
        withInvitationRadio.addEventListener('change', toggleMemberType);
        emailRadio.addEventListener('change', toggleInvitationType);
        accountRadio.addEventListener('change', toggleInvitationType);

        // 初期状態設定
        toggleMemberType();
    });
</script>
@endpush