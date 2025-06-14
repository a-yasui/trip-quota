<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'travel_plan_id' => TravelPlan::factory(),
            'type' => fake()->randomElement(['CORE', 'BRANCH']),
            'name' => fake()->words(2, true).'グループ',
            'branch_key' => function (array $attributes) {
                return $attributes['type'] === 'BRANCH' ? Str::random(8) : null;
            },
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function core(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'CORE',
            'name' => '全体グループ',
            'branch_key' => null,
        ]);
    }

    public function branch(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'BRANCH',
            'name' => fake()->randomElement(['A班', 'B班', 'C班', '1班', '2班', '3班']).'グループ',
            'branch_key' => Str::random(8),
        ]);
    }

    public function forTravelPlan(TravelPlan $travelPlan): static
    {
        return $this->state(fn (array $attributes) => [
            'travel_plan_id' => $travelPlan->id,
        ]);
    }

    public function withBranchKey(string $branchKey): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'BRANCH',
            'branch_key' => $branchKey,
        ]);
    }
}
