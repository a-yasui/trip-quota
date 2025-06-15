<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemBranchGroupKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SystemBranchGroupKey>
 */
final class SystemBranchGroupKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SystemBranchGroupKey::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'key' => fake()->word,
            'group_id' => \App\Models\Group::factory(),
            'is_active' => fake()->randomNumber(1),
            'expires_at' => fake()->optional()->dateTime(),
        ];
    }
}
