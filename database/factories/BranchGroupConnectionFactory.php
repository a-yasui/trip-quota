<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BranchGroupConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\BranchGroupConnection>
 */
final class BranchGroupConnectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BranchGroupConnection::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
        ];
    }
}
