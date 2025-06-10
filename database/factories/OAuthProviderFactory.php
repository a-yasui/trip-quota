<?php

namespace Database\Factories;

use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OAuthProviderFactory extends Factory
{
    protected $model = OAuthProvider::class;

    public function definition(): array
    {
        $providers = ['google', 'github'];

        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement($providers),
            'provider_id' => fake()->unique()->randomNumber(9),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->optional()->sha256(),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }

    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'google',
        ]);
    }

    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'github',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHour(),
        ]);
    }
}
