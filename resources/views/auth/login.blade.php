<x-guest-layout>
    @section('title', 'ログイン')
    
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-bold text-center mb-6">ログイン</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('パスワード')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-lime-600 shadow-sm focus:ring-lime-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('ログイン状態を保持する') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <div>
                <a class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('register') }}">
                    {{ __('新規登録はこちら') }}
                </a>
            </div>
            
            <div class="flex items-center">
                @if (Route::has('password.request'))
                    <a class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('password.request') }}">
                        {{ __('パスワードをお忘れですか？') }}
                    </a>
                @endif

                <x-primary-button class="ms-3">
                    {{ __('ログイン') }}
                </x-primary-button>
            </div>
        </div>
    </form>

    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">または</span>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3">
            <div>
                <a href="{{ route('socialite.redirect', 'google') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.545 10.239v3.821h5.445c-0.712 2.315-2.647 3.972-5.445 3.972-3.332 0-6.033-2.701-6.033-6.032s2.701-6.032 6.033-6.032c1.498 0 2.866 0.549 3.921 1.453l2.814-2.814c-1.798-1.677-4.198-2.707-6.735-2.707-5.523 0-10 4.477-10 10s4.477 10 10 10c8.396 0 10.201-7.835 9.355-11.663h-9.355z"/>
                    </svg>
                    <span class="ml-2">Googleでログイン</span>
                </a>
            </div>

            <div>
                <a href="{{ route('socialite.redirect', 'facebook') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385h-3.047v-3.47h3.047v-2.642c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385c5.737-.9 10.125-5.864 10.125-11.854z"/>
                    </svg>
                    <span class="ml-2">Facebookでログイン</span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
