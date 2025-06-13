<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メンバー招待 - {{ $travelPlan->plan_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">メンバー招待</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $travelPlan->plan_name }}にメンバーを招待します。</p>
        </div>

        <!-- エラーメッセージ -->
        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.members.store', $travelPlan->uuid) }}" class="space-y-6 p-6">
                @csrf

                <!-- 招待方法選択 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        招待方法 <span class="text-red-500">*</span>
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

                <!-- 表示名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        表示名
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="メンバー一覧で表示される名前（省略可）">
                    <p class="mt-1 text-sm text-gray-500">
                        省略した場合、メールアドレスまたはアカウントの表示名が使用されます
                    </p>
                    @error('name')
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

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.members.index', $travelPlan->uuid) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        招待を送信
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 招待方法の切り替え
        document.addEventListener('DOMContentLoaded', function() {
            const emailRadio = document.getElementById('invitation_type_email');
            const accountRadio = document.getElementById('invitation_type_account');
            const emailSection = document.getElementById('email_section');
            const accountSection = document.getElementById('account_section');

            function toggleSections() {
                if (emailRadio.checked) {
                    emailSection.style.display = 'block';
                    accountSection.style.display = 'none';
                    document.getElementById('email').required = true;
                    document.getElementById('account_name').required = false;
                } else {
                    emailSection.style.display = 'none';
                    accountSection.style.display = 'block';
                    document.getElementById('email').required = false;
                    document.getElementById('account_name').required = true;
                }
            }

            emailRadio.addEventListener('change', toggleSections);
            accountRadio.addEventListener('change', toggleSections);

            // 初期状態設定
            toggleSections();
        });
    </script>
</body>
</html>