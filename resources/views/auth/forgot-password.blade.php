<x-guest-layout>
    @section('title', 'パスワードリセット')
    
    <h2 class="text-2xl font-bold text-center mb-6">パスワードをお忘れですか？</h2>
    
    <div class="mb-4 text-sm text-gray-600">
        {{ __('メールアドレスを入力してください。パスワードリセット用のリンクをメールでお送りします。') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('login') }}">
                {{ __('ログイン画面に戻る') }}
            </a>
            
            <x-primary-button>
                {{ __('リセットリンクを送信') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
