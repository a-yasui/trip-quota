<?php $__env->startSection('title', 'メンバー管理 - ' . $travelPlan->plan_name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container'); ?>
        <?php $__env->startComponent('components.page-header', ['title' => 'メンバー管理', 'subtitle' => $travelPlan->plan_name]); ?>
            <?php $__env->slot('action'); ?>
                <a href="<?php echo e(route('travel-plans.members.create', $travelPlan->uuid)); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    メンバーを招待
                </a>
            <?php $__env->endSlot(); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 確認済みメンバー -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">確認済みメンバー (<?php echo e($confirmedMembers->count()); ?>)</h2>
                </div>
                <div class="px-6 py-4">
                    <?php if($confirmedMembers->count() > 0): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $confirmedMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-green-700">
                                                <?php echo e(substr($member->name, 0, 1)); ?>

                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900"><?php echo e($member->name); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo e($member->email); ?></p>
                                            <?php if($travelPlan->owner_user_id === $member->user_id): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    所有者
                                                </span>
                                            <?php endif; ?>
                                            <?php if($travelPlan->creator_user_id === $member->user_id): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-1">
                                                    作成者
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="<?php echo e(route('travel-plans.members.show', [$travelPlan->uuid, $member->id])); ?>" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            詳細
                                        </a>
                                        <?php if($member->user_id !== $travelPlan->owner_user_id): ?>
                                            <form method="POST" action="<?php echo e(route('travel-plans.members.destroy', [$travelPlan->uuid, $member->id])); ?>" 
                                                  onsubmit="return confirm('本当に削除しますか？')" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    削除
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">確認済みメンバーがいません</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 未確認メンバー・招待中 -->
            <div class="space-y-6">
                <!-- 未確認メンバー -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">未確認メンバー (<?php echo e($unconfirmedMembers->count()); ?>)</h2>
                    </div>
                    <div class="px-6 py-4">
                        <?php if($unconfirmedMembers->count() > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $unconfirmedMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between p-3 border border-yellow-200 rounded-lg bg-yellow-50">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-yellow-700">
                                                    <?php echo e(substr($member->name, 0, 1)); ?>

                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900"><?php echo e($member->name); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo e($member->email); ?></p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-yellow-600">確認待ち</span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-4">未確認メンバーはいません</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 招待中 -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">招待中 (<?php echo e($pendingInvitations->count()); ?>)</h2>
                    </div>
                    <div class="px-6 py-4">
                        <?php if($pendingInvitations->count() > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $pendingInvitations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between p-3 border border-blue-200 rounded-lg bg-blue-50">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo e($invitation->invitee_name ?? $invitation->invitee_email); ?>

                                            </p>
                                            <p class="text-sm text-gray-500"><?php echo e($invitation->invitee_email); ?></p>
                                            <p class="text-xs text-gray-400">
                                                有効期限: <?php echo e($invitation->expires_at->format('Y/m/d H:i')); ?>

                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs text-blue-600">招待中</span>
                                            <button class="text-gray-400 hover:text-gray-600 text-xs" 
                                                    onclick="alert('招待キャンセル機能は今後実装予定です')">
                                                キャンセル
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-4">送信中の招待はありません</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="<?php echo e(route('travel-plans.show', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
            <a href="<?php echo e(route('travel-plans.groups.index', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                グループ管理
            </a>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/members/index.blade.php ENDPATH**/ ?>