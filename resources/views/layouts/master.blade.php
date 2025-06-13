<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TripQuota')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- ロゴ -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-blue-600">
                        TripQuota
                    </a>
                </div>

                <!-- ナビゲーションメニュー -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="{{ route('dashboard') }}" 
                           class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            ダッシュボード
                        </a>
                        <a href="{{ route('invitations.index') }}" 
                           class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            招待
                        </a>
                    </div>
                </div>

                <!-- ユーザーメニュー -->
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-sm text-gray-600 hover:text-gray-900">
                                ログアウト
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" 
                           class="text-sm text-gray-600 hover:text-gray-900">
                            ログイン
                        </a>
                        <a href="{{ route('register') }}" 
                           class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md">
                            新規登録
                        </a>
                    @endauth
                </div>

                <!-- モバイルメニューボタン -->
                <div class="md:hidden">
                    <button type="button" 
                            class="text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900"
                            onclick="toggleMobileMenu()">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- モバイルメニュー -->
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-gray-200">
                <a href="{{ route('dashboard') }}" 
                   class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">
                    ダッシュボード
                </a>
                <a href="{{ route('invitations.index') }}" 
                   class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">
                    招待
                </a>
            </div>
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <main>
        @yield('content')
    </main>

    <!-- フッター -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} TripQuota. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // グローバルな成功メッセージの自動非表示
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.bg-green-100');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                    successAlert.style.opacity = '0';
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>