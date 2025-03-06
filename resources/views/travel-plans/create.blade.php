@extends('layouts.app')

@section('title', '旅行計画作成')

@section('header', '旅行計画作成')

@section('content')
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('travel-plans.store') }}">
                        @csrf

                        <!-- 旅行名 -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700">旅行名</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('title') border-red-500 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">例: 韓国ソウル旅行、沖縄社員旅行</p>
                        </div>

                        <!-- 出発日 -->
                        <div class="mb-6">
                            <label for="departure_date" class="block text-sm font-medium text-gray-700">出発日</label>
                            <input type="date" name="departure_date" id="departure_date" value="{{ old('departure_date') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('departure_date') border-red-500 @enderror">
                            @error('departure_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">旅行の開始日を選択してください</p>
                        </div>

                        <!-- 帰宅日（オプション） -->
                        <div class="mb-6">
                            <label for="return_date" class="block text-sm font-medium text-gray-700">帰宅日（オプション）</label>
                            <input type="date" name="return_date" id="return_date" value="{{ old('return_date') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('return_date') border-red-500 @enderror">
                            @error('return_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">旅行の終了日（未定の場合は空欄可）</p>
                        </div>

                        <!-- タイムゾーン -->
                        <div class="mb-6">
                            <label for="timezone" class="block text-sm font-medium text-gray-700">タイムゾーン</label>
                            <select name="timezone" id="timezone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500 focus:ring-opacity-50 @error('timezone') border-red-500 @enderror">
                                <option value="Asia/Tokyo" {{ old('timezone', 'Asia/Tokyo') == 'Asia/Tokyo' ? 'selected' : '' }}>日本時間 (Asia/Tokyo)</option>
                                <option value="Asia/Seoul" {{ old('timezone') == 'Asia/Seoul' ? 'selected' : '' }}>韓国時間 (Asia/Seoul)</option>
                                <option value="Asia/Shanghai" {{ old('timezone') == 'Asia/Shanghai' ? 'selected' : '' }}>中国時間 (Asia/Shanghai)</option>
                                <option value="Asia/Singapore" {{ old('timezone') == 'Asia/Singapore' ? 'selected' : '' }}>シンガポール時間 (Asia/Singapore)</option>
                                <option value="Asia/Bangkok" {{ old('timezone') == 'Asia/Bangkok' ? 'selected' : '' }}>タイ時間 (Asia/Bangkok)</option>
                                <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>イギリス時間 (Europe/London)</option>
                                <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>フランス時間 (Europe/Paris)</option>
                                <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>アメリカ東部時間 (America/New_York)</option>
                                <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>アメリカ西部時間 (America/Los_Angeles)</option>
                                <option value="Pacific/Honolulu" {{ old('timezone') == 'Pacific/Honolulu' ? 'selected' : '' }}>ハワイ時間 (Pacific/Honolulu)</option>
                            </select>
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">旅行先のタイムゾーンを選択してください</p>
                        </div>

                        <div class="flex items-center justify-between mt-8">
                            <a href="{{ route('travel-plans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                キャンセル
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-lime-400 active:bg-lime-600 focus:outline-none focus:border-lime-600 focus:ring ring-lime-300 disabled:opacity-25 transition ease-in-out duration-150">
                                旅行計画を作成
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
