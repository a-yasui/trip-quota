<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>招待詳細 - {{ $invitation->travelPlan->plan_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">旅行プランへの招待</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $invitation->invitedBy->name }}さんからの招待です</p>
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

        <!-- 招待詳細 -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-8">
                <div class="text-center mb-8">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $invitation->travelPlan->plan_name }}</h2>
                </div>

                <!-- 旅行プラン詳細 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">旅行詳細</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">出発日</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->travelPlan->departure_date->format('Y年m月d日（D）') }}</dd>
                            </div>
                            @if($invitation->travelPlan->return_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">帰国日</dt>
                                    <dd class="text-sm text-gray-900">{{ $invitation->travelPlan->return_date->format('Y年m月d日（D）') }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">タイムゾーン</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->travelPlan->timezone }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">参加者数</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->travelPlan->members->count() }}人</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">招待情報</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">招待者</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->invitedBy->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">送信日</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->created_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">有効期限</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->expires_at->format('Y年m月d日 H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">招待先グループ</dt>
                                <dd class="text-sm text-gray-900">{{ $invitation->group->name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- 説明 -->
                @if($invitation->travelPlan->description)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">旅行の説明</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $invitation->travelPlan->description }}</p>
                        </div>
                    </div>
                @endif

                <!-- アクションボタン -->
                <div class="flex items-center justify-center space-x-4">
                    <form method="POST" action="{{ route('invitations.accept', $invitation->invitation_token) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md text-base font-medium">
                            参加する
                        </button>
                    </form>
                    <form method="POST" action="{{ route('invitations.decline', $invitation->invitation_token) }}" 
                          onsubmit="return confirm('本当に拒否しますか？')" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-md text-base font-medium">
                            拒否する
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 注意事項 -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">ご注意</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>招待を受諾すると、この旅行プランのメンバーとして登録されます</li>
                            <li>メンバーになると、旅行プランの詳細やメンバー情報を閲覧できます</li>
                            <li>有効期限を過ぎると、この招待は無効になります</li>
                            <li>一度拒否すると、再度参加することはできません</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="flex justify-center">
            <a href="{{ route('invitations.index') }}" class="text-blue-600 hover:text-blue-800">
                ← 招待一覧に戻る
            </a>
        </div>
    </div>
</body>
</html>