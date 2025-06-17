@extends('layouts.master')

@section('title', 'アカウント設定')

@section('content')
    @component('components.container')
        @component('components.page-header', ['title' => 'アカウント設定', 'subtitle' => 'アカウント情報を管理します。'])
        @endcomponent

        @include('components.alerts')

        <div class="space-y-6">
            <!-- 基本情報 -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">基本情報</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">メールアドレス</label>
                            <div class="mt-1 text-sm text-gray-900">{{ $user->email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">登録日</label>
                            <div class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('Y年m月d日') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- アカウント一覧 -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">アカウント一覧</h3>
                </div>
                <div class="px-6 py-4">
                    @if($user->accounts->count() > 0)
                        <div class="space-y-3">
                            @foreach($user->accounts as $account)
                                <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                                    @if($account->thumbnail_url)
                                        <img src="{{ $account->thumbnail_url }}" alt="{{ $account->display_name }}" class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">{{ substr($account->account_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $account->display_name }}</p>
                                        <p class="text-sm text-gray-500">{{ '@' . $account->account_name }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">アカウントが設定されていません。</p>
                    @endif
                </div>
            </div>

            <!-- OAuth連携情報 -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">OAuth連携</h3>
                </div>
                <div class="px-6 py-4">
                    @if($user->oauthProviders->count() > 0)
                        <div class="space-y-2">
                            @foreach($user->oauthProviders as $provider)
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($provider->provider) }}
                                    </span>
                                    <span class="text-sm text-gray-600">連携済み</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">OAuth連携がありません。</p>
                    @endif
                </div>
            </div>

            <!-- パスワード変更 -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">パスワード変更</h3>
                </div>
                <div class="px-6 py-4">
                    <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">
                                現在のパスワード
                            </label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('current_password') border-red-300 @enderror"
                                   required>
                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                新しいパスワード
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('password') border-red-300 @enderror"
                                   required>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                新しいパスワード（確認）
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   required>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                パスワードを変更
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
                ← ダッシュボードに戻る
            </a>
        </div>
    @endcomponent
@endsection