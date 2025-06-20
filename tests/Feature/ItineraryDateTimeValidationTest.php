<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItineraryDateTimeValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TravelPlan $travelPlan;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
            'departure_date' => Carbon::now()->addDays(1),
            'return_date' => Carbon::now()->addDays(5),
        ]);
        $this->member = Member::factory()->forUser($this->user)->forTravelPlan($this->travelPlan)->create();
    }

    public function test_same_day_end_time_before_start_time_validation()
    {
        // 同日での到着時刻が出発時刻より前の場合はエラー
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '09:00', // 開始時刻より前
            ]
        );

        $response->assertSessionHasErrors('end_time');
    }

    public function test_same_day_same_time_validation()
    {
        // 同日での同じ時刻はエラー
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '10:00',
            ]
        );

        $response->assertSessionHasErrors('end_time');
    }

    public function test_next_day_early_morning_arrival_is_valid()
    {
        // 日をまたいでの翌日早朝到着は有効
        $departureDate = $this->travelPlan->departure_date;
        $arrivalDate = $departureDate->copy()->addDay();

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Overnight Journey',
                'date' => $departureDate->format('Y-m-d'),
                'arrival_date' => $arrivalDate->format('Y-m-d'),
                'start_time' => '23:30', // 23:30出発
                'end_time' => '06:00',   // 翌日06:00到着
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('end_time');
    }

    public function test_next_day_arrival_with_earlier_time_is_valid()
    {
        // 翌日の早い時刻での到着は有効
        $departureDate = $this->travelPlan->departure_date;
        $arrivalDate = $departureDate->copy()->addDay();

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Night Flight',
                'date' => $departureDate->format('Y-m-d'),
                'arrival_date' => $arrivalDate->format('Y-m-d'),
                'start_time' => '22:00', // 22:00出発
                'end_time' => '08:00',   // 翌日08:00到着
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('end_time');
    }

    public function test_arrival_datetime_before_departure_datetime_is_invalid()
    {
        // 到着日時が出発日時より前の場合はエラー
        $departureDate = $this->travelPlan->departure_date;
        $arrivalDate = $departureDate->copy()->subDay(); // 前日

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Invalid Journey',
                'date' => $departureDate->format('Y-m-d'),
                'arrival_date' => $arrivalDate->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '15:00', // 前日の15:00到着
            ]
        );

        $response->assertSessionHasErrors('end_time');
    }

    public function test_excessive_travel_duration_warning()
    {
        // 移動時間が48時間を超える場合は警告
        $departureDate = $this->travelPlan->departure_date;
        $arrivalDate = $departureDate->copy()->addDays(3); // 3日後

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Very Long Journey',
                'date' => $departureDate->format('Y-m-d'),
                'arrival_date' => $arrivalDate->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '11:00', // 3日後の11:00到着（73時間の移動）
            ]
        );

        $response->assertSessionHasErrors('end_time');
    }

    public function test_long_but_reasonable_travel_duration_is_valid()
    {
        // 長時間だが合理的な移動時間（48時間以内）は有効
        $departureDate = $this->travelPlan->departure_date;
        $arrivalDate = $departureDate->copy()->addDay(); // 翌日

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Long Journey',
                'date' => $departureDate->format('Y-m-d'),
                'arrival_date' => $arrivalDate->format('Y-m-d'),
                'start_time' => '08:00',
                'end_time' => '20:00', // 翌日20:00到着（36時間の移動）
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('end_time');
    }

    public function test_no_arrival_date_defaults_to_same_day()
    {
        // 到着日が未指定の場合は同日として扱われる
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Same Day Journey',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '17:00', // 同日17:00到着
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('end_time');
    }
}
