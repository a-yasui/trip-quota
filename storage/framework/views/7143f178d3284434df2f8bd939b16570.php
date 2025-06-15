<?php $__env->startSection('title', $group->name . ' - ' . $travelPlan->plan_name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container', ['class' => 'max-w-5xl']); ?>
        <?php $__env->startComponent('components.page-header'); ?>
            <?php $__env->slot('title'); ?>
                <?php if($group->type === 'CORE'): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 mr-3">
                        全体
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mr-3">
                        班
                    </span>
                <?php endif; ?>
                <?php echo e($group->name); ?>

            <?php $__env->endSlot(); ?>
            <?php $__env->slot('subtitle'); ?><?php echo e($travelPlan->plan_name); ?><?php $__env->endSlot(); ?>
            <?php $__env->slot('action'); ?>
                <?php if($group->group_type === 'BRANCH'): ?>
                    <a href="<?php echo e(route('travel-plans.groups.edit', [$travelPlan->uuid, $group->id])); ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        編集
                    </a>
                <?php endif; ?>
            <?php $__env->endSlot(); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- グループ詳細 -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 基本情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">基本情報</h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">グループ名</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($group->name); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">グループタイプ</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php if($group->group_type === 'CORE'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            全体グループ
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            班グループ
                                        </span>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <?php if($group->group_type === 'BRANCH'): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">班キー</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono"><?php echo e($group->branch_key); ?></dd>
                                </div>
                            <?php endif; ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">メンバー数</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($group->members->count()); ?>人</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">作成日</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($group->created_at->format('Y年m月d日 H:i')); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- 説明 -->
                <?php if($group->description): ?>
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">説明</h2>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($group->description); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- メンバー一覧 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">メンバー一覧</h2>
                    </div>
                    <div class="px-6 py-4">
                        <?php if($group->members->count() > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $group->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    <?php echo e(substr($member->name, 0, 1)); ?>

                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900"><?php echo e($member->name); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo e($member->email); ?></p>
                                                <div class="flex items-center space-x-2 mt-1">
                                                    <?php if($travelPlan->owner_user_id === $member->user_id): ?>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                            所有者
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if($travelPlan->creator_user_id === $member->user_id): ?>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            作成者
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if($member->is_confirmed): ?>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            確認済み
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            未確認
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="<?php echo e(route('travel-plans.members.show', [$travelPlan->uuid, $member->id])); ?>" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">このグループにはメンバーがいません</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- サイドバー -->
            <div class="space-y-6">
                <!-- アクション -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">アクション</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <a href="<?php echo e(route('travel-plans.members.create', $travelPlan->uuid)); ?>" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                            メンバーを招待
                        </a>
                        <?php if($group->group_type === 'BRANCH'): ?>
                            <a href="<?php echo e(route('travel-plans.groups.edit', [$travelPlan->uuid, $group->id])); ?>" 
                               class="w-full bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-center block">
                                グループ情報を編集
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 統計情報 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">統計</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">総メンバー数</dt>
                                <dd class="text-sm font-medium text-gray-900"><?php echo e($group->members->count()); ?>人</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">確認済み</dt>
                                <dd class="text-sm font-medium text-green-600"><?php echo e($group->members->where('is_confirmed', true)->count()); ?>人</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">未確認</dt>
                                <dd class="text-sm font-medium text-yellow-600"><?php echo e($group->members->where('is_confirmed', false)->count()); ?>人</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="<?php echo e(route('travel-plans.groups.index', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                ← グループ一覧に戻る
            </a>
            <a href="<?php echo e(route('travel-plans.show', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                旅行プラン詳細
            </a>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/groups/show.blade.php ENDPATH**/ ?>