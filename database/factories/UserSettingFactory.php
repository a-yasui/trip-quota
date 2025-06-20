<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    protected $model = UserSetting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'language' => 'ja',
            'timezone' => 'Asia/Tokyo',
            'email_notifications' => fake()->boolean(),
            'push_notifications' => fake()->boolean(),
            'currency' => fake()->randomElement(['JPY', 'USD', 'EUR']),
        ];
    }
}
