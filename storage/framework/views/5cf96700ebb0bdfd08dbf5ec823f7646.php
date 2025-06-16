<?php $__env->startSection('title', '受信した招待 - TripQuota'); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container', ['class' => 'max-w-5xl']); ?>
        <?php $__env->startComponent('components.page-header', ['title' => '受信した招待', 'subtitle' => 'あなた宛に送信された旅行プランの招待一覧です。']); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- 招待一覧 -->
        <?php if($pendingInvitations->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">待機中の招待 (<?php echo e($pendingInvitations->count()); ?>)</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $pendingInvitations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <?php echo e($invitation->travelPlan->plan_name); ?>

                                            </h3>
                                            <div class="mt-1 text-sm text-gray-500">
                                                <p>招待者: <?php echo e($invitation->invitedBy->name); ?></p>
                                                <p>送信日: <?php echo e($invitation->created_at->format('Y年m月d日 H:i')); ?></p>
                                                <p>有効期限: <?php echo e($invitation->expires_at->format('Y年m月d日 H:i')); ?></p>
                                            </div>
                                            <?php if($invitation->travelPlan->description): ?>
                                                <p class="mt-2 text-sm text-gray-700"><?php echo e(Str::limit($invitation->travelPlan->description, 100)); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <a href="<?php echo e(route('invitations.show', $invitation->invitation_token)); ?>" 
                                       class="bg-white hover:bg-gray-50 text-gray-700 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium">
                                        詳細を見る
                                    </a>
                                    <form method="POST" action="<?php echo e(route('invitations.accept', $invitation->invitation_token)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                            参加する
                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('invitations.decline', $invitation->invitation_token)); ?>" 
                                          onsubmit="return confirm('本当に拒否しますか？')" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" 
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                            拒否する
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- 旅行プラン詳細 -->
                            <div class="mt-4 pl-14">
                                <div class="flex items-center text-sm text-gray-500 space-x-6">
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>
                                            <?php echo e($invitation->travelPlan->departure_date->format('Y年m月d日')); ?>

                                            <?php if($invitation->travelPlan->return_date): ?>
                                                〜 <?php echo e($invitation->travelPlan->return_date->format('Y年m月d日')); ?>

                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span><?php echo e($invitation->travelPlan->members->count()); ?>人参加予定</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span><?php echo e($invitation->travelPlan->timezone); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">招待がありません</h3>
                <p class="mt-1 text-sm text-gray-500">現在、受信している招待はありません。</p>
            </div>
        <?php endif; ?>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center">
            <a href="<?php echo e(route('dashboard')); ?>" class="text-blue-600 hover:text-blue-800">
                ← ダッシュボードに戻る
            </a>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/invitations/index.blade.php ENDPATH**/ ?>