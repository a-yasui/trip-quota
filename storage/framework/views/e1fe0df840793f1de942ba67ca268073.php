<!-- ページヘッダー -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e($title); ?></h1>
            <?php if(isset($subtitle)): ?>
                <p class="mt-2 text-sm text-gray-600"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php if(isset($action)): ?>
            <div>
                <?php echo e($action); ?>

            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /Users/yasui/develop/trip-quota/resources/views/components/page-header.blade.php ENDPATH**/ ?>