declare(strict_types=1);

namespace <?php echo e($namespace); ?>;

use <?php echo e($name); ?>;
<?php if(isset($properties['remember_token'])): ?>
use Illuminate\Support\Str;
<?php endif; ?>
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\<?php echo e($name); ?>>
 */
final class <?php echo e($shortName); ?>Factory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = <?php echo e($shortName); ?>::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
<?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            '<?php echo e($name); ?>' => <?php echo $property; ?>,
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ];
    }
}
<?php /**PATH /Users/yasui/develop/trip-quota/vendor/thedoctor0/laravel-factory-generator/src/../resources/views/factory.blade.php ENDPATH**/ ?>