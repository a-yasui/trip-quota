<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItineraryTimezoneTest extends TestCase
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

    public function test_can_create_itinerary_with_departure_timezone()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'departure_timezone' => 'Asia/Tokyo',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'Test Itinerary',
            'departure_timezone' => 'Asia/Tokyo',
        ]);
    }

    public function test_can_create_itinerary_with_arrival_timezone()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'arrival_timezone' => 'UTC',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'Test Itinerary',
            'arrival_timezone' => 'UTC',
        ]);
    }

    public function test_can_create_itinerary_with_both_timezones()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'International Flight',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'departure_timezone' => 'Asia/Tokyo',
                'arrival_timezone' => 'America/New_York',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'International Flight',
            'departure_timezone' => 'Asia/Tokyo',
            'arrival_timezone' => 'America/New_York',
        ]);
    }

    public function test_timezone_fields_are_optional()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Local Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                // タイムゾーンフィールドは設定しない
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'Local Itinerary',
            'departure_timezone' => null,
            'arrival_timezone' => null,
        ]);
    }

    public function test_can_update_itinerary_with_timezones()
    {
        $itinerary = \App\Models\Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'date' => $this->travelPlan->departure_date,
            'departure_timezone' => null,
            'arrival_timezone' => null,
        ]);

        $response = $this->actingAs($this->user)->put(
            route('travel-plans.itineraries.update', [$this->travelPlan->uuid, $itinerary->id]),
            [
                'title' => 'Updated with Timezones',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'departure_timezone' => 'Asia/Seoul',
                'arrival_timezone' => 'Europe/London',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'id' => $itinerary->id,
            'title' => 'Updated with Timezones',
            'departure_timezone' => 'Asia/Seoul',
            'arrival_timezone' => 'Europe/London',
        ]);
    }

    public function test_timezone_validation_string_length()
    {
        $longTimezone = str_repeat('a', 51); // 50文字を超える

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'departure_timezone' => $longTimezone,
            ]
        );

        $response->assertSessionHasErrors('departure_timezone');
    }

    public function test_edit_form_populates_timezone_fields()
    {
        $itinerary = \App\Models\Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'title' => 'Test Itinerary',
            'date' => $this->travelPlan->departure_date,
            'departure_timezone' => 'Asia/Tokyo',
            'arrival_timezone' => 'UTC',
        ]);

        $response = $this->actingAs($this->user)->get(
            route('travel-plans.itineraries.edit', [$this->travelPlan->uuid, $itinerary->id])
        );

        $response->assertOk();
        $response->assertSee('name="departure_timezone"', false);
        $response->assertSee('name="arrival_timezone"', false);
        $response->assertSee('value="Asia/Tokyo"', false);
        $response->assertSee('value="UTC"', false);
    }
}
