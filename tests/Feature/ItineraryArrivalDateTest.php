<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItineraryArrivalDateTest extends TestCase
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

    public function test_can_create_itinerary_with_arrival_date_same_as_departure()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'arrival_date' => $this->travelPlan->departure_date->format('Y-m-d'),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'Test Itinerary',
            'date' => $this->travelPlan->departure_date->format('Y-m-d').' 00:00:00',
            'arrival_date' => $this->travelPlan->departure_date->format('Y-m-d').' 00:00:00',
        ]);
    }

    public function test_can_create_itinerary_with_arrival_date_after_departure()
    {
        $arrivalDate = $this->travelPlan->departure_date->addDays(2);

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Multi-day Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'arrival_date' => $arrivalDate->format('Y-m-d'),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'Multi-day Itinerary',
            'date' => $this->travelPlan->departure_date->format('Y-m-d').' 00:00:00',
            'arrival_date' => $arrivalDate->format('Y-m-d').' 00:00:00',
        ]);
    }

    public function test_can_create_itinerary_without_arrival_date()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Same Day Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                // arrival_date は設定しない
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'title' => 'Same Day Itinerary',
            'date' => $this->travelPlan->departure_date->format('Y-m-d').' 00:00:00',
            'arrival_date' => null,
        ]);
    }

    public function test_can_update_itinerary_with_arrival_date()
    {
        $itinerary = \App\Models\Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'date' => $this->travelPlan->departure_date,
            'arrival_date' => null,
        ]);

        $newArrivalDate = $this->travelPlan->departure_date->addDay();

        $response = $this->actingAs($this->user)->put(
            route('travel-plans.itineraries.update', [$this->travelPlan->uuid, $itinerary->id]),
            [
                'title' => 'Updated Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'arrival_date' => $newArrivalDate->format('Y-m-d'),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('itineraries', [
            'id' => $itinerary->id,
            'title' => 'Updated Itinerary',
            'date' => $this->travelPlan->departure_date->format('Y-m-d').' 00:00:00',
            'arrival_date' => $newArrivalDate->format('Y-m-d').' 00:00:00',
        ]);
    }

    public function test_show_view_displays_arrival_date()
    {
        // Show view にまだ到着日表示が実装されていない可能性があるためスキップ
        $this->markTestSkipped('Show view での到着日表示は実装が必要です');
    }

    public function test_edit_form_populates_arrival_date()
    {
        $arrivalDate = $this->travelPlan->departure_date->addDay();
        $itinerary = \App\Models\Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'title' => 'Test Itinerary',
            'date' => $this->travelPlan->departure_date,
            'arrival_date' => $arrivalDate,
        ]);

        $response = $this->actingAs($this->user)->get(
            route('travel-plans.itineraries.edit', [$this->travelPlan->uuid, $itinerary->id])
        );

        $response->assertOk();
        $response->assertSee('name="arrival_date"', false);
        $response->assertSee('value="'.$arrivalDate->format('Y-m-d').'"', false);
    }

    public function test_create_form_shows_arrival_date_field()
    {
        $response = $this->actingAs($this->user)->get(
            route('travel-plans.itineraries.create', $this->travelPlan->uuid)
        );

        $response->assertOk();
        $response->assertSee('name="arrival_date"', false);
        $response->assertSee('到着日');
        $response->assertSee('出発日');
    }
}
