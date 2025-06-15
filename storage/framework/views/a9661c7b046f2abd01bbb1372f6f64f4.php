<?php $__env->startSection('title', '旅程タイムライン - ' . $travelPlan->plan_name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container', ['class' => 'max-w-7xl']); ?>
        <?php $__env->startComponent('components.page-header', ['title' => '旅程タイムライン', 'subtitle' => $travelPlan->plan_name . 'のスケジュール']); ?>
            <?php $__env->slot('action'); ?>
                <div class="flex space-x-3">
                    <a href="<?php echo e(route('travel-plans.itineraries.index', $travelPlan->uuid)); ?>" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        リスト表示
                    </a>
                    <a href="<?php echo e(route('travel-plans.itineraries.create', $travelPlan->uuid)); ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        旅程を追加
                    </a>
                </div>
            <?php $__env->endSlot(); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- 日付範囲フィルター -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                        <input type="date" name="start_date" id="start_date" 
                               value="<?php echo e(request('start_date', $startDate->format('Y-m-d'))); ?>" 
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex-1 min-w-0">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                        <input type="date" name="end_date" id="end_date" 
                               value="<?php echo e(request('end_date', $endDate->format('Y-m-d'))); ?>" 
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex space-x-2 pt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            期間変更
                        </button>
                        <a href="<?php echo e(route('travel-plans.itineraries.timeline', $travelPlan->uuid)); ?>" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                            リセット
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- タイムライン表示 -->
        <?php if($itinerariesByDate->count() > 0): ?>
            <div class="space-y-8">
                <?php $__currentLoopData = $itinerariesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $dayItineraries): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dateObj = \Carbon\Carbon::parse($date);
                        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
                    ?>
                    
                    <div class="bg-white shadow-sm rounded-lg">
                        <!-- 日付ヘッダー -->
                        <div class="px-6 py-4 bg-blue-50 border-b border-blue-200 rounded-t-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-blue-900">
                                        <?php echo e($dateObj->format('n月d日')); ?>（<?php echo e($dayOfWeek); ?>）
                                    </h2>
                                    <p class="text-sm text-blue-700 mt-1"><?php echo e($dayItineraries->count()); ?>件の旅程</p>
                                </div>
                                <a href="<?php echo e(route('travel-plans.itineraries.create', $travelPlan->uuid)); ?>?date=<?php echo e($date); ?>" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium">
                                    この日に追加
                                </a>
                            </div>
                        </div>

                        <!-- 旅程リスト -->
                        <div class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $dayItineraries->sortBy(['start_time', 'created_at']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itinerary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="px-6 py-4 hover:bg-gray-50">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <!-- 時刻表示 -->
                                                <div class="w-24 flex-shrink-0">
                                                    <?php if($itinerary->start_time): ?>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo e($itinerary->start_time->format('H:i')); ?>

                                                        </div>
                                                        <?php if($itinerary->end_time): ?>
                                                            <div class="text-xs text-gray-500">
                                                                ～<?php echo e($itinerary->end_time->format('H:i')); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-sm text-gray-400">時間未定</div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- 交通手段アイコン -->
                                                <div class="w-8 h-8 mx-4 flex-shrink-0 rounded-full flex items-center justify-center <?php echo e($itinerary->transportation_type ? 'bg-blue-100' : 'bg-gray-100'); ?>">
                                                    <?php switch($itinerary->transportation_type):
                                                        case ('airplane'): ?>
                                                            <span class="text-blue-600">✈️</span>
                                                            <?php break; ?>
                                                        <?php case ('car'): ?>
                                                            <span class="text-blue-600">🚗</span>
                                                            <?php break; ?>
                                                        <?php case ('bus'): ?>
                                                            <span class="text-blue-600">🚌</span>
                                                            <?php break; ?>
                                                        <?php case ('ferry'): ?>
                                                            <span class="text-blue-600">⛴️</span>
                                                            <?php break; ?>
                                                        <?php case ('bike'): ?>
                                                            <span class="text-blue-600">🚲</span>
                                                            <?php break; ?>
                                                        <?php case ('walking'): ?>
                                                            <span class="text-blue-600">🚶</span>
                                                            <?php break; ?>
                                                        <?php default: ?>
                                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                    <?php endswitch; ?>
                                                </div>

                                                <!-- 旅程内容 -->
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-lg font-medium text-gray-900 truncate">
                                                        <a href="<?php echo e(route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id])); ?>" 
                                                           class="hover:text-blue-600">
                                                            <?php echo e($itinerary->title); ?>

                                                        </a>
                                                    </h3>
                                                    
                                                    <div class="mt-1 flex items-center flex-wrap gap-3 text-sm text-gray-500">
                                                        <?php if($itinerary->group): ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo e($itinerary->group->type === 'CORE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                                                <?php if($itinerary->group->type === 'CORE'): ?>
                                                                    [全体] <?php echo e($itinerary->group->name); ?>

                                                                <?php else: ?>
                                                                    [班] <?php echo e($itinerary->group->name); ?>

                                                                <?php endif; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($itinerary->departure_location || $itinerary->arrival_location): ?>
                                                            <span class="flex items-center">
                                                                <?php if($itinerary->departure_location): ?>
                                                                    <?php echo e($itinerary->departure_location); ?>

                                                                <?php endif; ?>
                                                                <?php if($itinerary->departure_location && $itinerary->arrival_location): ?>
                                                                    →
                                                                <?php endif; ?>
                                                                <?php if($itinerary->arrival_location): ?>
                                                                    <?php echo e($itinerary->arrival_location); ?>

                                                                <?php endif; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if($itinerary->members->count() > 0): ?>
                                                            <span class="flex items-center">
                                                                <svg class="flex-shrink-0 mr-1 h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                                </svg>
                                                                <?php echo e($itinerary->members->count()); ?>人参加
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if($itinerary->description): ?>
                                                        <p class="mt-2 text-sm text-gray-700 line-clamp-2">
                                                            <?php echo e(Str::limit($itinerary->description, 150)); ?>

                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if($itinerary->flight_number): ?>
                                                        <div class="mt-2 text-sm text-blue-600 font-medium">
                                                            <?php echo e($itinerary->airline ? $itinerary->airline . ' ' : ''); ?><?php echo e($itinerary->flight_number); ?>

                                                            <?php if($itinerary->departure_time && $itinerary->arrival_time): ?>
                                                                <span class="text-gray-500 ml-2">
                                                                    <?php echo e($itinerary->departure_time->format('H:i')); ?> - <?php echo e($itinerary->arrival_time->format('H:i')); ?>

                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- アクションボタン -->
                                        <div class="flex items-center space-x-2 ml-4">
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">指定期間に旅程がありません</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?php echo e($startDate->format('Y年n月d日')); ?> から <?php echo e($endDate->format('Y年n月d日')); ?> の期間に旅程がありません。
                </p>
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

<?php $__env->startPush('styles'); ?>
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/itineraries/timeline.blade.php ENDPATH**/ ?>