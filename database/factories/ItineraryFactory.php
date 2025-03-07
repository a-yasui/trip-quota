<?php

namespace Database\Factories;

use App\Enums\Transportation;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Itinerary>
 */
class ItineraryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'travel_plan_id' => TravelPlan::factory(),
            'transportation_type' => $this->faker->randomElement(Transportation::cases())->value,
            'departure_location' => $this->faker->city(),
            'arrival_location' => $this->faker->city(),
            'departure_time' => $this->faker->dateTimeBetween('now', '+1 week'),
            'arrival_time' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'company_name' => $this->faker->company(),
            'reference_number' => $this->faker->bothify('??###'),
            'notes' => $this->faker->paragraph(),
        ];
    }
}
