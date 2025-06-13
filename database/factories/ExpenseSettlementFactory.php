<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExpenseSettlement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ExpenseSettlement>
 */
final class ExpenseSettlementFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = ExpenseSettlement::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'travel_plan_id' => \App\Models\TravelPlan::factory(),
            'payer_member_id' => \App\Models\Member::factory(),
            'payee_member_id' => \App\Models\Member::factory(),
            'amount' => fake()->word,
            'currency' => fake()->currencyCode,
            'is_settled' => fake()->randomNumber(1),
            'settled_at' => fake()->optional()->dateTime(),
        ];
    }
}
