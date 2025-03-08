<?php

namespace Database\Factories;

use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['core', 'branch']),
            'travel_plan_id' => TravelPlan::factory(),
            'parent_group_id' => null,
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the group is a core group.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function core()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'core',
                'parent_group_id' => null,
            ];
        });
    }

    /**
     * Indicate that the group is a branch group.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function branch()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'branch',
            ];
        });
    }
}
