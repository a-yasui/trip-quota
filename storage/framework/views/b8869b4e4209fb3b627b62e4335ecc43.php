<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ダッシュボード - <?php echo e(config('app.name', 'TripQuota')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900"><?php echo e(config('app.name', 'TripQuota')); ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700"><?php echo e(Auth::user()->email); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                            ログアウト
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <?php echo e(session('success')); ?>

                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="px-4 py-6 sm:px-0">
            <div class="border-4 border-dashed border-gray-200 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">ダッシュボード</h2>
                <p class="text-gray-600 mb-6"><?php echo e(Auth::user()->email); ?> としてログインしています。</p>
                
                <!-- アカウント情報 -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">アカウント情報</h3>
                        <?php if(Auth::user()->accounts->count() > 0): ?>
                            <div class="space-y-2">
                                <?php $__currentLoopData = Auth::user()->accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center space-x-3">
                                        <?php if($account->thumbnail_url): ?>
                                            <img src="<?php echo e($account->thumbnail_url); ?>" alt="<?php echo e($account->display_name); ?>" class="w-8 h-8 rounded-full">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700"><?php echo e(substr($account->account_name, 0, 1)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo e($account->display_name); ?></p>
                                            <p class="text-sm text-gray-500">{{ $account->account_name }}</p>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">アカウントが設定されていません。</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- OAuth連携情報 -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">OAuth連携</h3>
                        <?php if(Auth::user()->oauthProviders->count() > 0): ?>
                            <div class="space-y-2">
                                <?php $__currentLoopData = Auth::user()->oauthProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo e(ucfirst($provider->provider)); ?>

                                        </span>
                                        <span class="text-sm text-gray-600">連携済み</span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">OAuth連携がありません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/dashboard.blade.php ENDPATH**/ ?>