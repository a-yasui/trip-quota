<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Expense>
 */
final class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'travel_plan_id' => \App\Models\TravelPlan::factory(),
            'group_id' => \App\Models\Group::factory(),
            'paid_by_member_id' => \App\Models\Member::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->text,
            'amount' => fake()->randomFloat(2, 10, 10000),
            'currency' => fake()->randomElement(['JPY', 'USD', 'EUR', 'KRW', 'CNY']),
            'expense_date' => fake()->date(),
        ];
    }
}
