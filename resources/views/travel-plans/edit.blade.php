<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $travelPlan->plan_name }}の編集 - TripQuota</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">旅行プランを編集</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $travelPlan->plan_name }}の設定を変更します。</p>
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
            <form method="POST" action="{{ route('travel-plans.update', $travelPlan->uuid) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- 旅行プラン名 -->
                <div>
                    <label for="plan_name" class="block text-sm font-medium text-gray-700">
                        旅行プラン名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="plan_name" 
                           name="plan_name" 
                           value="{{ old('plan_name', $travelPlan->plan_name) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="例：沖縄旅行2024">
                    @error('plan_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 出発日 -->
                <div>
                    <label for="departure_date" class="block text-sm font-medium text-gray-700">
                        出発日 <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="departure_date" 
                           name="departure_date" 
                           value="{{ old('departure_date', $travelPlan->departure_date->format('Y-m-d')) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('departure_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 帰国日 -->
                <div>
                    <label for="return_date" class="block text-sm font-medium text-gray-700">
                        帰国日
                    </label>
                    <input type="date" 
                           id="return_date" 
                           name="return_date" 
                           value="{{ old('return_date', $travelPlan->return_date?->format('Y-m-d')) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">未定の場合は空欄にしてください</p>
                    @error('return_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- タイムゾーン -->
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700">
                        タイムゾーン
                    </label>
                    <select id="timezone" 
                            name="timezone"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="Asia/Tokyo" {{ old('timezone', $travelPlan->timezone) === 'Asia/Tokyo' ? 'selected' : '' }}>日本標準時 (JST)</option>
                        <option value="America/New_York" {{ old('timezone', $travelPlan->timezone) === 'America/New_York' ? 'selected' : '' }}>アメリカ東部時間 (EST)</option>
                        <option value="America/Los_Angeles" {{ old('timezone', $travelPlan->timezone) === 'America/Los_Angeles' ? 'selected' : '' }}>アメリカ太平洋時間 (PST)</option>
                        <option value="Europe/London" {{ old('timezone', $travelPlan->timezone) === 'Europe/London' ? 'selected' : '' }}>グリニッジ標準時 (GMT)</option>
                        <option value="Europe/Paris" {{ old('timezone', $travelPlan->timezone) === 'Europe/Paris' ? 'selected' : '' }}>中央ヨーロッパ時間 (CET)</option>
                        <option value="Asia/Shanghai" {{ old('timezone', $travelPlan->timezone) === 'Asia/Shanghai' ? 'selected' : '' }}>中国標準時 (CST)</option>
                        <option value="Asia/Seoul" {{ old('timezone', $travelPlan->timezone) === 'Asia/Seoul' ? 'selected' : '' }}>韓国標準時 (KST)</option>
                    </select>
                    @error('timezone')
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
                              placeholder="旅行の目的や詳細を入力してください...">{{ old('description', $travelPlan->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- アクティブ状態 -->
                <div>
                    <div class="flex items-center">
                        <input id="is_active" 
                               name="is_active" 
                               type="checkbox" 
                               value="1"
                               {{ old('is_active', $travelPlan->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            アクティブな状態にする
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">非アクティブにすると、メンバーからは見えなくなります</p>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.show', $travelPlan->uuid) }}" 
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

        <!-- 危険ゾーン（所有者のみ） -->
        @if($travelPlan->owner_user_id === auth()->id())
            <div class="mt-8 bg-white shadow-sm rounded-lg border border-red-200">
                <div class="px-6 py-4 border-b border-red-200">
                    <h2 class="text-lg font-medium text-red-900">危険ゾーン</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-red-900">旅行プランを削除</h3>
                            <p class="text-sm text-red-700">この操作は取り消せません。慎重に行ってください。</p>
                        </div>
                        <form method="POST" action="{{ route('travel-plans.destroy', $travelPlan->uuid) }}" 
                              onsubmit="return confirm('本当に削除しますか？この操作は取り消せません。')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // 出発日の変更時に帰国日の最小値を更新
        document.getElementById('departure_date').addEventListener('change', function() {
            const departureDate = this.value;
            const returnDateInput = document.getElementById('return_date');
            if (departureDate) {
                returnDateInput.min = departureDate;
                // 帰国日が出発日より前の場合はクリア
                if (returnDateInput.value && returnDateInput.value <= departureDate) {
                    returnDateInput.value = '';
                }
            }
        });
        
        // 初期設定
        document.addEventListener('DOMContentLoaded', function() {
            const departureDateInput = document.getElementById('departure_date');
            departureDateInput.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>