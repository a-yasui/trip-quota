<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CurrencyExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CurrencyExchangeRate>
 */
final class CurrencyExchangeRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CurrencyExchangeRate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'from_currency' => fake()->word,
            'to_currency' => fake()->word,
            'rate' => fake()->word,
            'effective_date' => fake()->date(),
        ];
    }
}
