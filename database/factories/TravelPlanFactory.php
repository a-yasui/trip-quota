<?php

namespace Database\Factories;

use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TravelPlanFactory extends Factory
{
    protected $model = TravelPlan::class;

    public function definition(): array
    {
        $departureDate = fake()->dateTimeBetween('now', '+1 year');
        $returnDate = fake()->dateTimeBetween($departureDate, $departureDate->format('Y-m-d').' +2 weeks');

        return [
            'uuid' => Str::uuid(),
            'plan_name' => fake()->words(3, true).'旅行',
            'creator_user_id' => User::factory(),
            'owner_user_id' => function (array $attributes) {
                return $attributes['creator_user_id'];
            },
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'timezone' => fake()->randomElement(['Asia/Tokyo', 'UTC', 'America/New_York', 'Europe/London']),
            'is_active' => true,
            'description' => fake()->optional()->paragraph(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDifferentOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_user_id' => User::factory(),
        ]);
    }

    public function shortTrip(): static
    {
        return $this->state(function (array $attributes) {
            $departureDate = fake()->dateTimeBetween('now', '+6 months');
            $returnDate = fake()->dateTimeBetween($departureDate, $departureDate->format('Y-m-d').' +3 days');

            return [
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
            ];
        });
    }

    public function longTrip(): static
    {
        return $this->state(function (array $attributes) {
            $departureDate = fake()->dateTimeBetween('now', '+6 months');
            $returnDate = fake()->dateTimeBetween($departureDate->format('Y-m-d').' +2 weeks', $departureDate->format('Y-m-d').' +2 months');

            return [
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
            ];
        });
    }
}
