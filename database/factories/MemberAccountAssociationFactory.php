<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MemberAccountAssociation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\MemberAccountAssociation>
 */
final class MemberAccountAssociationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MemberAccountAssociation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
        ];
    }
}
