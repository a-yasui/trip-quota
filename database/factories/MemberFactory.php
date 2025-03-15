<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $travelPlan = $group->travelPlan;

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'user_id' => $user->id,
            'group_id' => $group->id,
            'arrival_date' => $travelPlan->departure_date,
            'departure_date' => $travelPlan->return_date,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the member is registered.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function registered()
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create();
            return [
                'user_id' => $user->id,
            ];
        });
    }

    /**
     * Indicate that the member is not registered.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unregistered()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
            ];
        });
    }

    /**
     * Indicate that the member is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the member is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
