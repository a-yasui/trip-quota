<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Accommodation>
 */
final class AccommodationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Accommodation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'travel_plan_id' => \App\Models\TravelPlan::factory(),
            'created_by_member_id' => \App\Models\Member::factory(),
            'name' => fake()->name,
            'address' => fake()->optional()->address,
            'check_in_date' => fake()->date(),
            'check_out_date' => fake()->date(),
            'check_in_time' => fake()->optional()->time(),
            'check_out_time' => fake()->optional()->time(),
            'timezone' => fake()->optional(0.4)->randomElement(['Asia/Tokyo', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Seoul', 'Europe/Paris']),
            'price_per_night' => fake()->optional()->randomFloat(2, 100, 50000),
            'currency' => fake()->randomElement(['JPY', 'USD', 'EUR', 'KRW', 'CNY']),
            'notes' => fake()->optional()->text,
            'confirmation_number' => fake()->optional()->word,
        ];
    }
}
