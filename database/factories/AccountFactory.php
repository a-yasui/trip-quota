<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        $accountName = 'user' . fake()->unique()->randomNumber(4);
        
        return [
            'user_id' => User::factory(),
            'account_name' => $accountName,
            'display_name' => fake()->name(),
            'thumbnail_url' => fake()->optional()->imageUrl(100, 100, 'people'),
            'bio' => fake()->optional()->text(200),
        ];
    }
}