<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>班グループ編集 - {{ $group->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">班グループ編集</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $travelPlan->plan_name }} - {{ $group->name }}</p>
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
            <form method="POST" action="{{ route('travel-plans.groups.update', [$travelPlan->uuid, $group->id]) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- グループ名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        班グループ名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $group->name) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="例：A班、観光組、自由行動グループ">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        説明
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                              placeholder="このグループの目的や活動内容を入力してください...">{{ old('description', $group->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 現在の情報 -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h3 class="text-sm font-medium text-gray-800 mb-3">現在の情報</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500">班キー</dt>
                            <dd class="font-mono text-gray-900">{{ $group->branch_key }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">メンバー数</dt>
                            <dd class="text-gray-900">{{ $group->members->count() }}人</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">作成日</dt>
                            <dd class="text-gray-900">{{ $group->created_at->format('Y年m月d日') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">最終更新</dt>
                            <dd class="text-gray-900">{{ $group->updated_at->format('Y年m月d日') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- 警告 -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">編集に関する注意</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>班キーは変更できません</li>
                                    <li>グループ名の変更は既存メンバーにも影響します</li>
                                    <li>メンバーの追加・削除は別途メンバー管理画面で行ってください</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <a href="{{ route('travel-plans.groups.show', [$travelPlan->uuid, $group->id]) }}" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            キャンセル
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            更新
                        </button>
                    </div>
                    
                    <!-- 削除ボタン -->
                    <form method="POST" action="{{ route('travel-plans.groups.destroy', [$travelPlan->uuid, $group->id]) }}" 
                          onsubmit="return confirm('本当にこの班グループを削除しますか？\n\n削除すると以下の影響があります：\n・このグループに所属するメンバーは全体グループに移動されます\n・グループ固有の行程や費用情報は削除されます\n・この操作は取り消せません')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            グループを削除
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</body>
</html>