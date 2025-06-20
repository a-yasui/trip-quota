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
     */
    public function definition(): array
    {
        return [
            'travel_plan_id' => \App\Models\TravelPlan::factory(),
            'payer_member_id' => \App\Models\Member::factory(),
            'payee_member_id' => \App\Models\Member::factory(),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'currency' => fake()->randomElement(['JPY', 'USD', 'EUR', 'KRW', 'CNY']),
            'settled_at' => fake()->boolean() ? fake()->optional()->dateTime() : null,
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (ExpenseSettlement $settlement) {
            // Ensure is_settled is never set during creation
            unset($settlement->is_settled);
        });
    }
}
