<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelPlan>
 */
class TravelPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $departureDate = $this->faker->dateTimeBetween('+1 month', '+2 months');
        $returnDate = $this->faker->dateTimeBetween(
            $departureDate->format('Y-m-d').' +1 day',
            $departureDate->format('Y-m-d').' +10 days'
        );

        return [
            'title' => $this->faker->sentence(3),
            'creator_id' => $user->id,
            'deletion_permission_holder_id' => $user->id,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'timezone' => $this->faker->randomElement(['Asia/Tokyo', 'Asia/Seoul', 'Asia/Shanghai', 'Asia/Singapore', 'Europe/London']),
            'is_active' => true,
        ];
    }
}
