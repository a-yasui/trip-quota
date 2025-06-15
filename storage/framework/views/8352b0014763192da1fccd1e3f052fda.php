<?php $__env->startSection('title', 'メンバー編集 - ' . $member->name); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.container', ['class' => 'max-w-3xl']); ?>
        <?php $__env->startComponent('components.page-header', ['title' => 'メンバー編集', 'subtitle' => $travelPlan->plan_name . ' - ' . $member->name]); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- フォーム -->
        <div class="bg-white shadow-sm rounded-lg">
            <form method="POST" action="<?php echo e(route('travel-plans.members.update', [$travelPlan->uuid, $member->id])); ?>" class="space-y-6 p-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- 表示名 -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        表示名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo e(old('name', $member->name)); ?>"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="メンバー一覧で表示される名前">
                    <?php $__errorArgs = ['name'];
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

                <!-- メールアドレス -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        メールアドレス <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo e(old('email', $member->email)); ?>"
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="example@example.com">
                    <?php $__errorArgs = ['email'];
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

                <!-- 現在の情報 -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h3 class="text-sm font-medium text-gray-800 mb-3">現在の情報</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-gray-500">ステータス</dt>
                            <dd class="text-gray-900">
                                <?php if($member->status === 'CONFIRMED'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        確認済み
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        未確認
                                    </span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">所属グループ数</dt>
                            <dd class="text-gray-900"><?php echo e($member->groups->count()); ?>個</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">参加日</dt>
                            <dd class="text-gray-900"><?php echo e($member->created_at->format('Y年m月d日')); ?></dd>
                        </div>
                        <?php if($member->confirmed_at): ?>
                            <div>
                                <dt class="text-gray-500">確認日</dt>
                                <dd class="text-gray-900"><?php echo e($member->confirmed_at->format('Y年m月d日')); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- 所属グループ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        所属グループ
                    </label>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $availableGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center">
                                <input id="group_<?php echo e($group->id); ?>" 
                                       name="groups[]" 
                                       type="checkbox" 
                                       value="<?php echo e($group->id); ?>"
                                       <?php echo e($member->groups->contains('id', $group->id) ? 'checked' : ''); ?>

                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="group_<?php echo e($group->id); ?>" class="ml-3 flex items-center">
                                    <?php if($group->type === 'CORE'): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                            全体
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            班
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-sm text-gray-900"><?php echo e($group->name); ?></span>
                                    <?php if($group->group_type === 'BRANCH'): ?>
                                        <span class="text-xs text-gray-500 ml-2">(<?php echo e($group->branch_key); ?>)</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__errorArgs = ['groups'];
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

                <!-- 権限情報 -->
                <?php if($travelPlan->owner_user_id === $member->user_id || $travelPlan->creator_user_id === $member->user_id): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">特別な権限</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>このメンバーは以下の特別な権限を持っています：</p>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        <?php if($travelPlan->owner_user_id === $member->user_id): ?>
                                            <li>所有者権限：このプランの完全な管理権限</li>
                                        <?php endif; ?>
                                        <?php if($travelPlan->creator_user_id === $member->user_id): ?>
                                            <li>作成者権限：このプランの作成者</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 警告 -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">編集に関する注意</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>メールアドレスを変更した場合、再度確認が必要になる場合があります</li>
                                    <li>グループから除外すると、そのグループの情報にアクセスできなくなります</li>
                                    <li>少なくとも1つのグループに所属している必要があります</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ボタン -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <a href="<?php echo e(route('travel-plans.members.show', [$travelPlan->uuid, $member->id])); ?>" 
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            キャンセル
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            更新
                        </button>
                    </div>
                    
                    <!-- 削除ボタン -->
                    <?php if($member->user_id !== $travelPlan->owner_user_id): ?>
                        <form method="POST" action="<?php echo e(route('travel-plans.members.destroy', [$travelPlan->uuid, $member->id])); ?>" 
                              onsubmit="return confirm('本当にこのメンバーを削除しますか？\n\n削除すると以下の影響があります：\n・このメンバーはすべてのグループから除外されます\n・メンバー関連の履歴は削除されます\n・この操作は取り消せません')" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" 
                                    class="bg-red-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                メンバーを削除
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/members/edit.blade.php ENDPATH**/ ?>