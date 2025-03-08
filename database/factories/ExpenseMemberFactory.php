<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseMember>
 */
class ExpenseMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'member_id' => Member::factory(),
            'share_amount' => $this->faker->numberBetween(500, 10000),
            'is_paid' => $this->faker->boolean(30), // 30%の確率で支払い済み
        ];
    }

    /**
     * 支払い済みの経費メンバーを作成
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => true,
        ]);
    }

    /**
     * 未払いの経費メンバーを作成
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_paid' => false,
        ]);
    }
}
