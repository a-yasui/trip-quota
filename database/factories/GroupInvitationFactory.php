<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GroupInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GroupInvitation>
 */
final class GroupInvitationFactory extends Factory
{
    /**
    * The name of the factory's corresponding model.
    *
    * @var string
    */
    protected $model = GroupInvitation::class;

    /**
    * Define the model's default state.
    *
    * @return array
    */
    public function definition(): array
    {
        return [
            'travel_plan_id' => \App\Models\TravelPlan::factory(),
            'group_id' => \App\Models\Group::factory(),
            'invited_by_member_id' => \App\Models\Member::factory(),
            'invitee_email' => fake()->word,
            'invitee_name' => fake()->optional()->word,
            'invitation_token' => fake()->word,
            'status' => fake()->randomElement(['pending', 'accepted', 'declined', 'expired']),
            'expires_at' => fake()->dateTime(),
            'responded_at' => fake()->optional()->dateTime(),
        ];
    }
}
