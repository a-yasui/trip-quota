@extends('layouts.master')

@section('title', '宿泊施設を編集 - ' . $accommodation->name)

@section('content')
    @component('components.container', ['class' => 'max-w-4xl'])
        @component('components.page-header', ['title' => '宿泊施設を編集', 'subtitle' => $accommodation->name . 'の情報を編集します。'])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.accommodations.update', [$travelPlan->uuid, $accommodation->id]) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- 宿泊施設名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        宿泊施設名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $accommodation->name) }}"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="ホテル名、旅館名など">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 住所 -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">
                        住所
                    </label>
                    <textarea id="address" 
                              name="address" 
                              rows="2"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                              placeholder="宿泊施設の住所">{{ old('address', $accommodation->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- チェックイン・チェックアウト -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="check_in_date" class="block text-sm font-medium text-gray-700">
                            チェックイン日 <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="check_in_date" 
                               name="check_in_date" 
                               value="{{ old('check_in_date', $accommodation->check_in_date->format('Y-m-d')) }}"
                               min="{{ $travelPlan->departure_date->format('Y-m-d') }}"
                               @if($travelPlan->return_date) max="{{ $travelPlan->return_date->format('Y-m-d') }}" @endif
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('check_in_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="check_out_date" class="block text-sm font-medium text-gray-700">
                            チェックアウト日 <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="check_out_date" 
                               name="check_out_date" 
                               value="{{ old('check_out_date', $accommodation->check_out_date->format('Y-m-d')) }}"
                               min="{{ $travelPlan->departure_date->format('Y-m-d') }}"
                               @if($travelPlan->return_date) max="{{ $travelPlan->return_date->format('Y-m-d') }}" @endif
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('check_out_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- チェックイン・チェックアウト時間 -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="check_in_time" class="block text-sm font-medium text-gray-700">
                            チェックイン時間
                        </label>
                        <input type="time" 
                               id="check_in_time" 
                               name="check_in_time" 
                               value="{{ old('check_in_time', $accommodation->check_in_time?->format('H:i') ?? '15:00') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('check_in_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="check_out_time" class="block text-sm font-medium text-gray-700">
                            チェックアウト時間
                        </label>
                        <input type="time" 
                               id="check_out_time" 
                               name="check_out_time" 
                               value="{{ old('check_out_time', $accommodation->check_out_time?->format('H:i') ?? '10:00') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('check_out_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- タイムゾーン -->
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700">
                        タイムゾーン
                        <span class="text-xs text-gray-500">(任意)</span>
                    </label>
                    <select name="timezone" id="timezone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">選択してください</option>
                        @foreach(\App\Enums\TimezoneEnum::options() as $value => $label)
                            <option value="{{ $value }}" {{ old('timezone', $accommodation->timezone) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">宿泊施設のタイムゾーンを選択してください。未選択の場合、日本時間として扱われます。</p>
                </div>

                <!-- 料金と通貨 -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label for="price_per_night" class="block text-sm font-medium text-gray-700">
                            1泊あたりの料金
                        </label>
                        <input type="number" 
                               id="price_per_night" 
                               name="price_per_night" 
                               value="{{ old('price_per_night', $accommodation->price_per_night) }}"
                               min="0"
                               step="0.01"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                               placeholder="10000">
                        @error('price_per_night')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">
                            通貨
                        </label>
                        <select id="currency" 
                                name="currency"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="JPY" {{ old('currency', $accommodation->currency) === 'JPY' ? 'selected' : '' }}>JPY (円)</option>
                            <option value="USD" {{ old('currency', $accommodation->currency) === 'USD' ? 'selected' : '' }}>USD (ドル)</option>
                            <option value="EUR" {{ old('currency', $accommodation->currency) === 'EUR' ? 'selected' : '' }}>EUR (ユーロ)</option>
                            <option value="KRW" {{ old('currency', $accommodation->currency) === 'KRW' ? 'selected' : '' }}>KRW (ウォン)</option>
                            <option value="CNY" {{ old('currency', $accommodation->currency) === 'CNY' ? 'selected' : '' }}>CNY (元)</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 予約番号 -->
                <div>
                    <label for="confirmation_number" class="block text-sm font-medium text-gray-700">
                        予約番号・確認番号
                    </label>
                    <input type="text" 
                           id="confirmation_number" 
                           name="confirmation_number" 
                           value="{{ old('confirmation_number', $accommodation->confirmation_number) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="予約確認番号など">
                    @error('confirmation_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- メンバー選択 -->
                @if($members->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            宿泊メンバー
                        </label>
                        <div class="space-y-2">
                            @foreach($members as $member)
                                <div class="flex items-center">
                                    <input id="member_{{ $member->id }}" 
                                           name="member_ids[]" 
                                           type="checkbox" 
                                           value="{{ $member->id }}"
                                           {{ in_array($member->id, old('member_ids', $accommodation->members->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="member_{{ $member->id }}" class="ml-2 block text-sm text-gray-900">
                                        {{ $member->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('member_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- メモ・備考 -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        メモ・備考
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                              placeholder="特記事項、連絡先、アクセス方法など">{{ old('notes', $accommodation->notes) }}</textarea>
                    @error('notes')
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
                            <h3 class="text-sm font-medium text-blue-800">宿泊施設の編集について</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>チェックアウト日はチェックイン日より後の日付を設定してください</li>
                                    <li>宿泊日程は旅行期間内である必要があります</li>
                                    <li>料金は1泊あたりの金額を入力してください（税込み・税抜きは備考欄に記載）</li>
                                    <li>編集内容は即座に反映されます</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.accommodations.show', [$travelPlan->uuid, $accommodation->id]) }}" 
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
    // チェックイン日が変更されたらチェックアウト日の最小値を更新
    document.addEventListener('DOMContentLoaded', function() {
        const checkInDate = document.getElementById('check_in_date');
        const checkOutDate = document.getElementById('check_out_date');

        checkInDate.addEventListener('change', function() {
            if (this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutDate.min = nextDay.toISOString().split('T')[0];
                
                // チェックアウト日がチェックイン日より前の場合は更新
                if (checkOutDate.value && checkOutDate.value <= this.value) {
                    checkOutDate.value = nextDay.toISOString().split('T')[0];
                }
            }
        });
    });
</script>
@endpush