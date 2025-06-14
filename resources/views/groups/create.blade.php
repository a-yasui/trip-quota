@extends('layouts.master')

@section('title', '班グループ作成 - ' . $travelPlan->plan_name)

@section('content')
    @component('components.container', ['class' => 'max-w-3xl'])
        @component('components.page-header', ['title' => '班グループ作成', 'subtitle' => $travelPlan->plan_name . 'の新しい班グループを作成します。'])
        @endcomponent

        @include('components.alerts')

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="{{ route('travel-plans.groups.store', $travelPlan->uuid) }}" class="space-y-6 p-6">
                @csrf

                <!-- グループ名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        班グループ名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
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
                              placeholder="このグループの目的や活動内容を入力してください...">{{ old('description') }}</textarea>
                    @error('description')
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
                            <h3 class="text-sm font-medium text-blue-800">班グループについて</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>班グループは特定のメンバーで構成される小さなグループです</li>
                                    <li>行程管理や費用管理などで班ごとに分けて管理することができます</li>
                                    <li>作成後にメンバーを招待・追加することができます</li>
                                    <li>一意の班キーが自動生成されます</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('travel-plans.groups.index', $travelPlan->uuid) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        班グループを作成
                    </button>
                </div>
            </form>
        </div>
    @endcomponent
@endsection