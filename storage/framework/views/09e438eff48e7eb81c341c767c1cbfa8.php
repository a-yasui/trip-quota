<?php $__env->startSection('title', 'グループ管理 - ' . $travelPlan->plan_name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container'); ?>
        <?php $__env->startComponent('components.page-header', ['title' => 'グループ管理', 'subtitle' => $travelPlan->plan_name]); ?>
            <?php $__env->slot('action'); ?>
                <a href="<?php echo e(route('travel-plans.groups.create', $travelPlan->uuid)); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    班グループを作成
                </a>
            <?php $__env->endSlot(); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- コアグループ -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                            コア
                        </span>
                        全体グループ
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <?php if($coreGroup): ?>
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-md font-medium text-gray-900 flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                        班
                                    </span>
                                    <?php echo e($coreGroup->name); ?>

                                </h3>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-500"><?php echo e($coreGroup->members->count()); ?>人</span>
                                    <a href="<?php echo e(route('travel-plans.groups.show', [$travelPlan->uuid, $coreGroup->id])); ?>" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        詳細
                                    </a>
                                </div>
                            </div>
                            <?php if($coreGroup->description): ?>
                                <p class="mt-2 text-sm text-gray-600"><?php echo e(Str::limit($coreGroup->description, 100)); ?></p>
                            <?php endif; ?>
                            <div class="mt-3 text-xs text-gray-400">
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-200 text-gray-600">
                                    削除不可
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">コアグループが見つかりません</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 班グループ -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-3">
                            班
                        </span>
                        班グループ (<?php echo e($branchGroups->count()); ?>)
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <?php if($branchGroups->count() > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $branchGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-md font-medium text-gray-900 flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                                班
                                            </span>
                                            <?php echo e($group->name); ?>

                                        </h3>
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-500"><?php echo e($group->members->count()); ?>人</span>
                                            <a href="<?php echo e(route('travel-plans.groups.show', [$travelPlan->uuid, $group->id])); ?>" 
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                詳細
                                            </a>
                                            <a href="<?php echo e(route('travel-plans.groups.edit', [$travelPlan->uuid, $group->id])); ?>" 
                                               class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                編集
                                            </a>
                                        </div>
                                    </div>
                                    <?php if($group->description): ?>
                                        <p class="mt-2 text-sm text-gray-600"><?php echo e(Str::limit($group->description, 100)); ?></p>
                                    <?php endif; ?>
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-xs text-gray-400">班キー: <?php echo e($group->branch_key); ?></span>
                                        <span class="text-xs text-gray-400"><?php echo e($group->created_at->format('Y/m/d')); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">班グループがありません</h3>
                            <p class="mt-1 text-sm text-gray-500">新しい班グループを作成してメンバーを分けて管理しましょう。</p>
                            <div class="mt-6">
                                <a href="<?php echo e(route('travel-plans.groups.create', $travelPlan->uuid)); ?>" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    班グループを作成
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="<?php echo e(route('travel-plans.show', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
            <a href="<?php echo e(route('travel-plans.members.index', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                メンバー管理
            </a>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/groups/index.blade.php ENDPATH**/ ?>