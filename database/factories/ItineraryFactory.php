<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItineraryFactory extends Factory
{
    protected $model = Itinerary::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('now', '+1 year');
        $startTime = fake()->optional()->time();
        $endTime = $startTime ? fake()->time() : null;

        // arrival_dateは出発日以降の日付を生成
        $arrivalDate = fake()->optional(0.3)->dateTimeBetween(
            $date, 
            Carbon::instance($date)->addDays(3)
        );

        return [
            'travel_plan_id' => TravelPlan::factory(),
            'group_id' => null,
            'created_by_member_id' => function (array $attributes) {
                return Member::factory()->for(TravelPlan::find($attributes['travel_plan_id']))->create()->id;
            },
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'date' => $date,
            'arrival_date' => $arrivalDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'timezone' => fake()->randomElement(['Asia/Tokyo', 'UTC', 'America/New_York', 'Europe/London']),
            'departure_timezone' => fake()->optional(0.4)->randomElement(['Asia/Tokyo', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Seoul', 'Europe/Paris']),
            'arrival_timezone' => fake()->optional(0.3)->randomElement(['Asia/Tokyo', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Seoul', 'Europe/Paris']),
            'transportation_type' => fake()->optional()->randomElement(['walking', 'bike', 'car', 'bus', 'ferry', 'airplane']),
            'airline' => fake()->optional()->randomElement(['JAL', 'ANA', 'JetStar', 'Peach', 'Skymark']),
            'flight_number' => fake()->optional()->bothify('??###'),
            'departure_time' => fake()->optional()->dateTime(),
            'arrival_time' => fake()->optional()->dateTime(),
            'departure_location' => fake()->optional()->city(),
            'arrival_location' => fake()->optional()->city(),
            'notes' => fake()->optional()->text(200),
        ];
    }

    public function withFlight(): static
    {
        return $this->state(fn (array $attributes) => [
            'transportation_type' => 'airplane',
            'airline' => fake()->randomElement(['JAL', 'ANA', 'JetStar', 'Peach', 'Skymark']),
            'flight_number' => fake()->bothify('??###'),
            'departure_time' => fake()->dateTimeBetween('now', '+1 year'),
            'arrival_time' => fake()->dateTimeBetween('now', '+1 year'),
            'departure_location' => fake()->randomElement(['羽田空港', '成田空港', '関西国際空港', '中部国際空港']),
            'arrival_location' => fake()->randomElement(['新千歳空港', '那覇空港', '福岡空港', '仙台空港']),
        ]);
    }

    public function withoutTransportation(): static
    {
        return $this->state(fn (array $attributes) => [
            'transportation_type' => null,
            'airline' => null,
            'flight_number' => null,
            'departure_time' => null,
            'arrival_time' => null,
            'departure_location' => null,
            'arrival_location' => null,
        ]);
    }

    public function forTravelPlan(TravelPlan $travelPlan): static
    {
        return $this->state(fn (array $attributes) => [
            'travel_plan_id' => $travelPlan->id,
        ]);
    }

    public function forGroup(Group $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group_id' => $group->id,
            'travel_plan_id' => $group->travel_plan_id,
        ]);
    }

    public function createdBy(Member $member): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by_member_id' => $member->id,
            'travel_plan_id' => $member->travel_plan_id,
        ]);
    }

    public function sightseeing(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->randomElement(['観光地見学', '美術館見学', '神社参拝', 'ショッピング', 'グルメ体験']),
            'transportation_type' => fake()->randomElement(['walking', 'bus', 'car']),
        ]);
    }

    public function accommodation(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->randomElement(['ホテルチェックイン', '旅館チェックイン', 'チェックアウト']),
            'transportation_type' => fake()->randomElement(['walking', 'car', 'bus']),
        ]);
    }
}
