<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($travelPlan->plan_name); ?> - TripQuota</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ヘッダー -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($travelPlan->plan_name); ?></h1>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>
                            <?php echo e($travelPlan->departure_date->format('Y年m月d日')); ?>

                            <?php if($travelPlan->return_date): ?>
                                〜 <?php echo e($travelPlan->return_date->format('Y年m月d日')); ?>

                            <?php endif; ?>
                        </span>
                        <span class="ml-4"><?php echo e($travelPlan->timezone); ?></span>
                    </div>
                    <?php if(!$travelPlan->is_active): ?>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                無効
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?php echo e(route('travel-plans.edit', $travelPlan->uuid)); ?>" 
                       class="bg-white hover:bg-gray-50 text-gray-700 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium">
                        編集
                    </a>
                    <?php if($travelPlan->owner_user_id === auth()->id()): ?>
                        <form method="POST" action="<?php echo e(route('travel-plans.destroy', $travelPlan->uuid)); ?>" 
                              onsubmit="return confirm('本当に削除しますか？この操作は取り消せません。')" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                削除
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 成功メッセージ -->
        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- エラーメッセージ -->
        <?php if($errors->any()): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- メインコンテンツ -->
            <div class="lg:col-span-2 space-y-8">
                <!-- 旅行プラン詳細 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">旅行プラン詳細</h2>
                    </div>
                    <div class="px-6 py-4">
                        <?php if($travelPlan->description): ?>
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($travelPlan->description); ?></p>
                        <?php else: ?>
                            <p class="text-gray-500 italic">説明はありません</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- グループ一覧 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">グループ</h2>
                    </div>
                    <div class="px-6 py-4">
                        <?php if($travelPlan->groups->count() > 0): ?>
                            <div class="space-y-4">
                                <?php $__currentLoopData = $travelPlan->groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="text-md font-medium text-gray-900">
                                                    <?php echo e($group->name); ?>

                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($group->type === 'CORE' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>">
                                                        <?php echo e($group->type === 'CORE' ? 'コア' : '班'); ?>

                                                    </span>
                                                </h3>
                                                <?php if($group->description): ?>
                                                    <p class="mt-1 text-sm text-gray-600"><?php echo e($group->description); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500">グループはまだ作成されていません。</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 今後の機能プレビュー -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">機能（準備中）</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 8v-1a1 1 0 011-1h1a1 1 0 011 1v1m-4 0h4"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">宿泊施設</p>
                            </div>
                            <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">行程管理</p>
                            </div>
                            <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">費用管理</p>
                            </div>
                            <div class="text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">書類管理</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- サイドバー -->
            <div class="space-y-6">
                <!-- 基本情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">作成者</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($travelPlan->creator->email); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">所有者</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($travelPlan->owner->email); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">UUID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono break-all"><?php echo e($travelPlan->uuid); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">作成日</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($travelPlan->created_at->format('Y年m月d日 H:i')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($travelPlan->updated_at->format('Y年m月d日 H:i')); ?></dd>
                        </div>
                    </div>
                </div>

                <!-- メンバー一覧 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">メンバー (<?php echo e($travelPlan->members->count()); ?>人)</h2>
                    </div>
                    <div class="px-6 py-4">
                        <?php if($travelPlan->members->count() > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $travelPlan->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                <?php echo e(substr($member->name, 0, 1)); ?>

                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900"><?php echo e($member->name); ?></p>
                                            <?php if(!$member->is_confirmed): ?>
                                                <p class="text-xs text-gray-500">未確認</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500">メンバーはいません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="<?php echo e(route('travel-plans.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン一覧に戻る
            </a>
        </div>
    </div>
</body>
</html><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/travel-plans/show.blade.php ENDPATH**/ ?>