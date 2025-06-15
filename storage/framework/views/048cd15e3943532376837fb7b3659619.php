<?php $__env->startSection('title', '旅程編集 - ' . $travelPlan->plan_name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container', ['class' => 'max-w-4xl']); ?>
        <?php $__env->startComponent('components.page-header', ['title' => '旅程編集', 'subtitle' => $travelPlan->plan_name . 'の旅程を編集します。']); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="<?php echo e(route('travel-plans.itineraries.update', [$travelPlan->uuid, $itinerary->id])); ?>" class="space-y-6 p-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- 左側：基本情報 -->
                    <div class="space-y-6">
                        <!-- タイトル -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                タイトル <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo e(old('title', $itinerary->title)); ?>"
                                   required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   placeholder="例：東京駅集合、チェックイン、観光スポット訪問">
                            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- 説明 -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                説明
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="詳細な内容、持参物、注意事項などを記載してください..."><?php echo e(old('description', $itinerary->description)); ?></textarea>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- 日付と時刻 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">
                                    日付 <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       id="date" 
                                       name="date" 
                                       value="<?php echo e(old('date', $itinerary->date->format('Y-m-d'))); ?>"
                                       min="<?php echo e($travelPlan->departure_date->format('Y-m-d')); ?>"
                                       <?php if($travelPlan->return_date): ?> max="<?php echo e($travelPlan->return_date->format('Y-m-d')); ?>" <?php endif; ?>
                                       required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">
                                    開始時刻
                                </label>
                                <input type="time" 
                                       id="start_time" 
                                       name="start_time" 
                                       value="<?php echo e(old('start_time', $itinerary->start_time?->format('H:i'))); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">
                                    終了時刻
                                </label>
                                <input type="time" 
                                       id="end_time" 
                                       name="end_time" 
                                       value="<?php echo e(old('end_time', $itinerary->end_time?->format('H:i'))); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- グループ選択 -->
                        <div>
                            <label for="group_id" class="block text-sm font-medium text-gray-700">
                                対象グループ
                            </label>
                            <select name="group_id" id="group_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">すべてのメンバー</option>
                                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($group->id); ?>" <?php echo e(old('group_id', $itinerary->group_id) == $group->id ? 'selected' : ''); ?>>
                                        <?php if($group->group_type === 'CORE'): ?>
                                            [全体] <?php echo e($group->name); ?>

                                        <?php else: ?>
                                            [班] <?php echo e($group->name); ?>

                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['group_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- 右側：交通手段・参加者 -->
                    <div class="space-y-6">
                        <!-- 交通手段 -->
                        <div>
                            <label for="transportation_type" class="block text-sm font-medium text-gray-700">
                                交通手段
                            </label>
                            <select name="transportation_type" id="transportation_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">選択してください</option>
                                <option value="walking" <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'walking' ? 'selected' : ''); ?>>徒歩</option>
                                <option value="bike" <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'bike' ? 'selected' : ''); ?>>自転車</option>
                                <option value="car" <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'car' ? 'selected' : ''); ?>>車</option>
                                <option value="bus" <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'bus' ? 'selected' : ''); ?>>バス</option>
                                <option value="ferry" <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'ferry' ? 'selected' : ''); ?>>フェリー</option>
                                <option value="airplane" <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'airplane' ? 'selected' : ''); ?>>飛行機</option>
                            </select>
                            <?php $__errorArgs = ['transportation_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- 飛行機詳細（条件表示） -->
                        <div id="airplane_details" style="display: <?php echo e(old('transportation_type', $itinerary->transportation_type) === 'airplane' ? 'block' : 'none'); ?>;">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="airline" class="block text-sm font-medium text-gray-700">
                                        航空会社
                                    </label>
                                    <input type="text" 
                                           id="airline" 
                                           name="airline" 
                                           value="<?php echo e(old('airline', $itinerary->airline)); ?>"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           placeholder="例：JAL、ANA">
                                    <?php $__errorArgs = ['airline'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label for="flight_number" class="block text-sm font-medium text-gray-700">
                                        便名
                                    </label>
                                    <input type="text" 
                                           id="flight_number" 
                                           name="flight_number" 
                                           value="<?php echo e(old('flight_number', $itinerary->flight_number)); ?>"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           placeholder="例：JL123">
                                    <?php $__errorArgs = ['flight_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <!-- 出発地・到着地 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="departure_location" class="block text-sm font-medium text-gray-700">
                                    出発地
                                </label>
                                <input type="text" 
                                       id="departure_location" 
                                       name="departure_location" 
                                       value="<?php echo e(old('departure_location', $itinerary->departure_location)); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="例：東京駅、ホテル">
                                <?php $__errorArgs = ['departure_location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label for="arrival_location" class="block text-sm font-medium text-gray-700">
                                    到着地
                                </label>
                                <input type="text" 
                                       id="arrival_location" 
                                       name="arrival_location" 
                                       value="<?php echo e(old('arrival_location', $itinerary->arrival_location)); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="例：観光地、空港">
                                <?php $__errorArgs = ['arrival_location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- 参加者選択 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                参加者
                            </label>
                            <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center">
                                        <input id="member_<?php echo e($member->id); ?>" 
                                               name="member_ids[]" 
                                               type="checkbox" 
                                               value="<?php echo e($member->id); ?>"
                                               <?php echo e(in_array($member->id, old('member_ids', $itinerary->members->pluck('id')->toArray())) ? 'checked' : ''); ?>

                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="member_<?php echo e($member->id); ?>" class="ml-3 text-sm text-gray-900">
                                            <?php echo e($member->name); ?>

                                        </label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                選択しない場合、作成者のみが参加者として設定されます
                            </p>
                            <?php $__errorArgs = ['member_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- メモ -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                メモ
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                      placeholder="その他のメモや特記事項..."><?php echo e(old('notes', $itinerary->notes)); ?></textarea>
                            <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="<?php echo e(route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id])); ?>" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        変更を保存
                    </button>
                </div>
            </form>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const transportationType = document.getElementById('transportation_type');
        const airplaneDetails = document.getElementById('airplane_details');

        function toggleAirplaneDetails() {
            if (transportationType.value === 'airplane') {
                airplaneDetails.style.display = 'block';
                document.getElementById('flight_number').required = true;
            } else {
                airplaneDetails.style.display = 'none';
                document.getElementById('flight_number').required = false;
            }
        }

        transportationType.addEventListener('change', toggleAirplaneDetails);
        
        // 初期状態の設定
        toggleAirplaneDetails();
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/itineraries/edit.blade.php ENDPATH**/ ?>