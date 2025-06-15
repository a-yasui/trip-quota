<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>旅行プラン一覧 - TripQuota</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">旅行プラン一覧</h1>
                <a href="<?php echo e(route('travel-plans.create')); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    新しい旅行プランを作成
                </a>
            </div>
            
            <!-- 検索・フィルタ -->
            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                <form method="GET" class="flex-1">
                    <input type="hidden" name="filter" value="<?php echo e($filter); ?>">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?php echo e($search); ?>"
                               placeholder="旅行プラン名で検索..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </form>
                
                <div class="flex gap-2">
                    <a href="<?php echo e(route('travel-plans.index', ['filter' => 'all'])); ?>" 
                       class="px-3 py-2 text-sm rounded-md <?php echo e($filter === 'all' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'); ?>">
                        すべて
                    </a>
                    <a href="<?php echo e(route('travel-plans.index', ['filter' => 'upcoming'])); ?>" 
                       class="px-3 py-2 text-sm rounded-md <?php echo e($filter === 'upcoming' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'); ?>">
                        今後の旅行
                    </a>
                    <a href="<?php echo e(route('travel-plans.index', ['filter' => 'active'])); ?>" 
                       class="px-3 py-2 text-sm rounded-md <?php echo e($filter === 'active' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'); ?>">
                        有効
                    </a>
                    <a href="<?php echo e(route('travel-plans.index', ['filter' => 'past'])); ?>" 
                       class="px-3 py-2 text-sm rounded-md <?php echo e($filter === 'past' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:text-gray-900'); ?>">
                        過去の旅行
                    </a>
                </div>
            </div>
        </div>

        <!-- 成功メッセージ -->
        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- 旅行プラン一覧 -->
        <?php if($travelPlans->count() > 0): ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $travelPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e(route('travel-plans.show', $plan->uuid)); ?>" class="block hover:bg-gray-50">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-lg font-medium text-blue-600 truncate">
                                                <?php echo e($plan->plan_name); ?>

                                            </p>
                                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>
                                                    <?php echo e($plan->departure_date->format('Y年m月d日')); ?>

                                                    <?php if($plan->return_date): ?>
                                                        〜 <?php echo e($plan->return_date->format('Y年m月d日')); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <?php if($plan->description): ?>
                                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                                    <?php echo e($plan->description); ?>

                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <?php if(!$plan->is_active): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    無効
                                                </span>
                                            <?php endif; ?>
                                            <span class="text-sm text-gray-500">
                                                <?php echo e($plan->members_count ?? $plan->members->count()); ?>人参加
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <!-- ページネーション -->
            <div class="mt-6">
                <?php echo e($travelPlans->links()); ?>

            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">旅行プランがありません</h3>
                <p class="mt-1 text-sm text-gray-500">新しい旅行プランを作成して始めましょう。</p>
                <div class="mt-6">
                    <a href="<?php echo e(route('travel-plans.create')); ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        新しい旅行プランを作成
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="<?php echo e(route('dashboard')); ?>" class="text-blue-600 hover:text-blue-800">
                ← ダッシュボードに戻る
            </a>
        </div>
    </div>
</body>
</html><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/travel-plans/index.blade.php ENDPATH**/ ?>