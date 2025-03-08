<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['food', 'transportation', 'accommodation', 'entertainment', 'shopping', 'other'];
        $currencies = ['JPY', 'USD', 'EUR', 'KRW', 'CNY', 'TWD'];

        return [
            'travel_plan_id' => TravelPlan::factory(),
            'payer_member_id' => Member::factory(),
            'description' => $this->faker->sentence(3),
            'amount' => $this->faker->numberBetween(1000, 50000),
            'currency' => $this->faker->randomElement($currencies),
            'expense_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'category' => $this->faker->randomElement($categories),
            'notes' => $this->faker->optional(0.7)->paragraph(1),
            'is_settled' => $this->faker->boolean(20), // 20%の確率で精算済み
        ];
    }

    /**
     * 精算済みの経費を作成
     */
    public function settled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_settled' => true,
        ]);
    }

    /**
     * 未精算の経費を作成
     */
    public function unsettled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_settled' => false,
        ]);
    }

    /**
     * 特定のカテゴリの経費を作成
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
