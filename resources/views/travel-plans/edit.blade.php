@extends('layouts.app')

@section('title', '旅行計画編集 - ' . $travelPlan->title)

@section('header', '旅行計画編集')

@section('content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('travel-plans.update', $travelPlan) }}">
                        @csrf
                        @method('PUT')

                        <!-- 旅行名 -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700">旅行名</label>
                            <input type="text" name="title" id="title" 
                                   value="{{ old('title', $travelPlan->title) }}" 
                                   {{ now()->startOfDay()->gte($travelPlan->departure_date) ? 'disabled' : 'required' }}
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('title') border-red-500 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                @if(now()->startOfDay()->gte($travelPlan->departure_date))
                                    旅行開始後は旅行名を変更できません
                                @else
                                    例: 韓国ソウル旅行、沖縄社員旅行
                                @endif
                            </p>
                        </div>

                        <!-- 出発日 -->
                        <div class="mb-6">
                            <label for="departure_date" class="block text-sm font-medium text-gray-700">出発日</label>
                            <input type="date" name="departure_date" id="departure_date" 
                                   value="{{ old('departure_date', $travelPlan->departure_date->format('Y-m-d')) }}" 
                                   {{ now()->startOfDay()->gte($travelPlan->departure_date) ? 'disabled' : 'required' }}
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('departure_date') border-red-500 @enderror">
                            @error('departure_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                @if(now()->startOfDay()->gte($travelPlan->departure_date))
                                    旅行開始後は出発日を変更できません
                                @else
                                    旅行の開始日を選択してください
                                @endif
                            </p>
                        </div>

                        <!-- 帰宅日（オプション） -->
                        <div class="mb-6">
                            <label for="return_date" class="block text-sm font-medium text-gray-700">帰宅日（オプション）</label>
                            <input type="date" name="return_date" id="return_date" 
                                   value="{{ old('return_date', $travelPlan->return_date ? $travelPlan->return_date->format('Y-m-d') : '') }}"
                                   {{ now()->startOfDay()->gte($travelPlan->departure_date) && $travelPlan->return_date ? 'disabled' : '' }}
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('return_date') border-red-500 @enderror">
                            @error('return_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                @if(now()->startOfDay()->gte($travelPlan->departure_date) && $travelPlan->return_date)
                                    旅行開始後は帰宅日を変更できません
                                @elseif(now()->startOfDay()->gte($travelPlan->departure_date) && !$travelPlan->return_date)
                                    旅行開始後も帰宅日を設定できます（一度設定すると変更不可）
                                @else
                                    旅行の終了日（未定の場合は空欄可）
                                @endif
                            </p>
                        </div>

                        <!-- タイムゾーン -->
                        <div class="mb-6">
                            <label for="timezone" class="block text-sm font-medium text-gray-700">タイムゾーン</label>
                            <select name="timezone" id="timezone"
                                    {{ now()->startOfDay()->gte($travelPlan->departure_date) ? 'disabled' : 'required' }}
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('timezone') border-red-500 @enderror">
                                @foreach(\App\Enums\Timezone::grouped() as $region => $timezones)
                                    <optgroup label="{{ $region }}">
                                        @foreach($timezones as $value => $label)
                                            <option value="{{ $value }}" {{ old('timezone', $travelPlan->timezone) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                @if(now()->startOfDay()->gte($travelPlan->departure_date))
                                    旅行開始後はタイムゾーンを変更できません
                                @else
                                    旅行先のタイムゾーンを選択してください
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center justify-between mt-8">
                            <a href="{{ route('travel-plans.show', $travelPlan) }}" class="btn-cancel">
                                キャンセル
                            </a>
                            <button type="submit" class="btn-submit">
                                更新する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
