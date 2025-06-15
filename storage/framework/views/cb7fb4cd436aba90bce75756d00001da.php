<?php $__env->startSection('title', '旅程管理 - ' . $travelPlan->plan_name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container'); ?>
        <?php $__env->startComponent('components.page-header', ['title' => '旅程管理', 'subtitle' => $travelPlan->plan_name]); ?>
            <?php $__env->slot('action'); ?>
                <div class="flex space-x-3">
                    <a href="<?php echo e(route('travel-plans.itineraries.timeline', $travelPlan->uuid)); ?>" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        タイムライン表示
                    </a>
                    <a href="<?php echo e(route('travel-plans.itineraries.create', $travelPlan->uuid)); ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        旅程を追加
                    </a>
                </div>
            <?php $__env->endSlot(); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- フィルター -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <label for="group_id" class="block text-sm font-medium text-gray-700 mb-1">グループでフィルター</label>
                        <select name="group_id" id="group_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">すべてのグループ</option>
                            <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($group->id); ?>" <?php echo e(request('group_id') == $group->id ? 'selected' : ''); ?>>
                                    <?php if($group->group_type === 'CORE'): ?>
                                        [全体] <?php echo e($group->name); ?>

                                    <?php else: ?>
                                        [班] <?php echo e($group->name); ?>

                                    <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">日付でフィルター</label>
                        <input type="date" name="date" id="date" value="<?php echo e(request('date')); ?>" 
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex space-x-2 pt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            フィルター適用
                        </button>
                        <a href="<?php echo e(route('travel-plans.itineraries.index', $travelPlan->uuid)); ?>" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            クリア
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 旅程一覧 -->
        <?php if($itineraries->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">旅程一覧 (<?php echo e($itineraries->count()); ?>件)</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $itineraries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itinerary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <?php if($itinerary->transportation_type === 'airplane'): ?>
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                            <?php elseif($itinerary->transportation_type === 'car'): ?>
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 6h3l2 3H8l2-3h3z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 17h14v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2z"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-lg font-medium text-gray-900"><?php echo e($itinerary->title); ?></h3>
                                            <div class="mt-1 flex items-center text-sm text-gray-500 space-x-4">
                                                <span class="flex items-center">
                                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <?php echo e($itinerary->date->format('Y年m月d日（D）')); ?>

                                                </span>
                                                <?php if($itinerary->start_time): ?>
                                                    <span class="flex items-center">
                                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <?php echo e($itinerary->start_time->format('H:i')); ?>

                                                        <?php if($itinerary->end_time): ?>
                                                            〜<?php echo e($itinerary->end_time->format('H:i')); ?>

                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if($itinerary->group): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($itinerary->group->group_type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                                        <?php echo e($itinerary->group->name); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($itinerary->description): ?>
                                                <p class="mt-2 text-sm text-gray-700"><?php echo e(Str::limit($itinerary->description, 100)); ?></p>
                                            <?php endif; ?>
                                            <?php if($itinerary->members->count() > 0): ?>
                                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                                    <svg class="flex-shrink-0 mr-1.5 h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    </svg>
                                                    参加者: <?php echo e($itinerary->members->pluck('name')->join(', ')); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 ml-4">
                                    <a href="<?php echo e(route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id])); ?>" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        詳細
                                    </a>
                                    <a href="<?php echo e(route('travel-plans.itineraries.edit', [$travelPlan->uuid, $itinerary->id])); ?>" 
                                       class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        編集
                                    </a>
                                    <form method="POST" action="<?php echo e(route('travel-plans.itineraries.destroy', [$travelPlan->uuid, $itinerary->id])); ?>" 
                                          onsubmit="return confirm('本当に削除しますか？')" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            削除
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">旅程がありません</h3>
                <p class="mt-1 text-sm text-gray-500">最初の旅程を作成して、旅行スケジュールを管理しましょう。</p>
                <div class="mt-6">
                    <a href="<?php echo e(route('travel-plans.itineraries.create', $travelPlan->uuid)); ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        旅程を追加
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ナビゲーション -->
        <div class="mt-8 flex justify-center space-x-6">
            <a href="<?php echo e(route('travel-plans.show', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                ← 旅行プラン詳細に戻る
            </a>
            <a href="<?php echo e(route('travel-plans.groups.index', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                グループ管理
            </a>
            <a href="<?php echo e(route('travel-plans.members.index', $travelPlan->uuid)); ?>" class="text-blue-600 hover:text-blue-800">
                メンバー管理
            </a>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/itineraries/index.blade.php ENDPATH**/ ?>