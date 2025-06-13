<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $user = User::factory();

        return [
            'travel_plan_id' => TravelPlan::factory(),
            'user_id' => $user,
            'account_id' => function (array $attributes) {
                return Account::factory()->for(User::find($attributes['user_id']))->create()->id;
            },
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'is_confirmed' => true,
        ];
    }

    public function unconfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confirmed' => false,
        ]);
    }

    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'account_id' => null,
        ]);
    }

    public function forTravelPlan(TravelPlan $travelPlan): static
    {
        return $this->state(fn (array $attributes) => [
            'travel_plan_id' => $travelPlan->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            $account = Account::factory()->for($user)->create();

            return [
                'user_id' => $user->id,
                'account_id' => $account->id,
                'name' => $user->accounts->first()->display_name ?? fake()->name(),
                'email' => $user->email,
            ];
        });
    }
}
