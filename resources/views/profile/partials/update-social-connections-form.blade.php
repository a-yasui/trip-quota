<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('ソーシャルアカウント連携') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('外部サービスのアカウントとの連携を管理します。') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <h3 class="text-md font-medium text-gray-900">{{ __('ソーシャルログイン') }}</h3>
        <p class="text-sm text-gray-600">{{ __('以下のサービスでログインできます。') }}</p>

        <div class="grid grid-cols-2 gap-3">
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

    <div class="mt-6 border-t border-gray-200 pt-6">
        <h3 class="text-md font-medium text-gray-900">{{ __('アカウント連携') }}</h3>
        <p class="text-sm text-gray-600">{{ __('外部サービスのアカウントと連携して、ログインを簡単にします。') }}</p>
        
        <div class="mt-6 space-y-6">
        <!-- Google -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-700" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.545 10.239v3.821h5.445c-0.712 2.315-2.647 3.972-5.445 3.972-3.332 0-6.033-2.701-6.033-6.032s2.701-6.032 6.033-6.032c1.498 0 2.866 0.549 3.921 1.453l2.814-2.814c-1.798-1.677-4.198-2.707-6.735-2.707-5.523 0-10 4.477-10 10s4.477 10 10 10c8.396 0 10.201-7.835 9.355-11.663h-9.355z"/>
                </svg>
                <span class="ml-2 text-gray-700">Google</span>
            </div>

            @if($user->oauthProviders && $user->oauthProviders->where('provider', 'google')->first())
                <div class="flex items-center">
                    <span class="text-green-600 mr-3">連携済み</span>
                    <form method="POST" action="{{ route('socialite.disconnect', 'google') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-900">
                            {{ __('連携解除') }}
                        </button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('socialite.connect', 'google') }}">
                    @csrf
                    <button type="submit" class="text-sm text-lime-600 hover:text-lime-900">
                        {{ __('連携する') }}
                    </button>
                </form>
            @endif
        </div>

        <!-- Facebook -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385h-3.047v-3.47h3.047v-2.642c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385c5.737-.9 10.125-5.864 10.125-11.854z"/>
                </svg>
                <span class="ml-2 text-gray-700">Facebook</span>
            </div>

            @if($user->oauthProviders && $user->oauthProviders->where('provider', 'facebook')->first())
                <div class="flex items-center">
                    <span class="text-green-600 mr-3">連携済み</span>
                    <form method="POST" action="{{ route('socialite.disconnect', 'facebook') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-900">
                            {{ __('連携解除') }}
                        </button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('socialite.connect', 'facebook') }}">
                    @csrf
                    <button type="submit" class="text-sm text-lime-600 hover:text-lime-900">
                        {{ __('連携する') }}
                    </button>
                </form>
            @endif
        </div>
        </div>
    </div>

    @if(session('status') === 'socialite-connected')
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2000)"
            class="mt-4 text-sm text-green-600"
        >{{ __('アカウントが連携されました。') }}</p>
    @endif

    @if(session('status') === 'socialite-disconnected')
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2000)"
            class="mt-4 text-sm text-green-600"
        >{{ __('アカウントの連携が解除されました。') }}</p>
    @endif

    @if($errors->has('socialite'))
        <p class="mt-4 text-sm text-red-600">{{ $errors->first('socialite') }}</p>
    @endif
</section>
