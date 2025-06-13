<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItineraryControllerTest extends TestCase
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
            'departure_date' => Carbon::parse('2024-01-15'),
            'return_date' => Carbon::parse('2024-01-20'),
        ]);
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_index_displays_itineraries_for_authenticated_member()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Test Itinerary',
            'date' => Carbon::parse('2024-01-16'),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程管理');
        $response->assertSee('Test Itinerary');
        $response->assertSee('旅程を追加');
    }

    public function test_index_filters_by_group()
    {
        $group = Group::factory()->create(['travel_plan_id' => $this->travelPlan->id]);
        $groupItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => $group->id,
            'title' => 'Group Itinerary',
            'created_by' => $this->user->id,
        ]);
        $generalItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'group_id' => null,
            'title' => 'General Itinerary',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid) . '?group_id=' . $group->id);

        $response->assertStatus(200);
        $response->assertSee('Group Itinerary');
        $response->assertDontSee('General Itinerary');
    }

    public function test_index_filters_by_date()
    {
        $targetDate = Carbon::parse('2024-01-16');
        $targetItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'date' => $targetDate,
            'title' => 'Target Date Itinerary',
            'created_by' => $this->user->id,
        ]);
        $otherItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'date' => Carbon::parse('2024-01-17'),
            'title' => 'Other Date Itinerary',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid) . '?date=2024-01-16');

        $response->assertStatus(200);
        $response->assertSee('Target Date Itinerary');
        $response->assertDontSee('Other Date Itinerary');
    }

    public function test_create_displays_form_for_authenticated_member()
    {
        $group = Group::factory()->create(['travel_plan_id' => $this->travelPlan->id]);
        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程作成');
        $response->assertSee('タイトル');
        $response->assertSee('交通手段');
        $response->assertSee($group->name);
        $response->assertSee($member->name);
    }

    public function test_create_sets_default_values_from_query_parameters()
    {
        $group = Group::factory()->create(['travel_plan_id' => $this->travelPlan->id]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.create', $this->travelPlan->uuid) . '?date=2024-01-16&group_id=' . $group->id);

        $response->assertStatus(200);
        $response->assertSee('value="2024-01-16"', false);
        $response->assertSee('selected', false);
    }

    public function test_store_creates_itinerary_with_valid_data()
    {
        $validData = [
            'title' => 'New Itinerary',
            'description' => 'Test description',
            'date' => '2024-01-16',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'transportation_type' => 'car',
            'departure_location' => 'Tokyo Station',
            'arrival_location' => 'Osaka Castle',
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan->uuid), $validData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('itineraries', [
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'New Itinerary',
            'description' => 'Test description',
            'transportation_type' => 'car',
            'departure_location' => 'Tokyo Station',
            'arrival_location' => 'Osaka Castle',
        ]);
    }

    public function test_store_creates_airplane_itinerary_with_flight_details()
    {
        $validData = [
            'title' => 'Flight to Osaka',
            'date' => '2024-01-16',
            'transportation_type' => 'airplane',
            'airline' => 'JAL',
            'flight_number' => 'JL123',
            'departure_location' => 'Haneda Airport',
            'arrival_location' => 'Kansai Airport',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan->uuid), $validData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('itineraries', [
            'title' => 'Flight to Osaka',
            'transportation_type' => 'airplane',
            'airline' => 'JAL',
            'flight_number' => 'JL123',
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan->uuid), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title', 'date']);
    }

    public function test_store_validates_end_time_after_start_time()
    {
        $invalidData = [
            'title' => 'Test Itinerary',
            'date' => '2024-01-16',
            'start_time' => '14:00',
            'end_time' => '13:00',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan->uuid), $invalidData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('end_time');
    }

    public function test_store_assigns_selected_members()
    {
        $member1 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'is_confirmed' => true,
        ]);
        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'is_confirmed' => true,
        ]);

        $validData = [
            'title' => 'Test Itinerary',
            'date' => '2024-01-16',
            'member_ids' => [$member1->id, $member2->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan->uuid), $validData);

        $response->assertStatus(302);
        
        $itinerary = Itinerary::where('title', 'Test Itinerary')->first();
        $this->assertCount(2, $itinerary->members);
        $this->assertTrue($itinerary->members->contains($member1));
        $this->assertTrue($itinerary->members->contains($member2));
    }

    public function test_show_displays_itinerary_details()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Test Itinerary',
            'description' => 'Test description',
            'transportation_type' => 'airplane',
            'airline' => 'JAL',
            'flight_number' => 'JL123',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.show', [$this->travelPlan->uuid, $itinerary->id]));

        $response->assertStatus(200);
        $response->assertSee('Test Itinerary');
        $response->assertSee('Test description');
        $response->assertSee('JAL');
        $response->assertSee('JL123');
        $response->assertSee('編集');
        $response->assertSee('削除');
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Test Itinerary',
            'transportation_type' => 'airplane',
            'airline' => 'JAL',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.edit', [$this->travelPlan->uuid, $itinerary->id]));

        $response->assertStatus(200);
        $response->assertSee('旅程編集');
        $response->assertSee('value="Test Itinerary"', false);
        $response->assertSee('value="JAL"', false);
        $response->assertSee('selected', false); // airplane option should be selected
    }

    public function test_update_modifies_itinerary_with_valid_data()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Original Title',
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'date' => $itinerary->date->format('Y-m-d'),
            'transportation_type' => 'bus',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.itineraries.update', [$this->travelPlan->uuid, $itinerary->id]), $updateData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('itineraries', [
            'id' => $itinerary->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'transportation_type' => 'bus',
        ]);
    }

    public function test_destroy_deletes_itinerary()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.itineraries.destroy', [$this->travelPlan->uuid, $itinerary->id]));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('itineraries', ['id' => $itinerary->id]);
    }

    public function test_timeline_displays_itineraries_grouped_by_date()
    {
        $date1 = Carbon::parse('2024-01-16');
        $date2 = Carbon::parse('2024-01-17');
        
        $itinerary1 = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Day 1 Activity',
            'date' => $date1,
            'start_time' => Carbon::parse('10:00'),
            'created_by' => $this->user->id,
        ]);
        
        $itinerary2 = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Day 2 Activity',
            'date' => $date2,
            'start_time' => Carbon::parse('14:00'),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程タイムライン');
        $response->assertSee('1月16日');
        $response->assertSee('1月17日');
        $response->assertSee('Day 1 Activity');
        $response->assertSee('Day 2 Activity');
        $response->assertSee('10:00');
        $response->assertSee('14:00');
    }

    public function test_timeline_filters_by_date_range()
    {
        $startDate = Carbon::parse('2024-01-16');
        $endDate = Carbon::parse('2024-01-18');

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid) . 
                '?start_date=' . $startDate->format('Y-m-d') . 
                '&end_date=' . $endDate->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee('value="2024-01-16"', false);
        $response->assertSee('value="2024-01-18"', false);
    }

    public function test_unauthorized_user_cannot_access_itineraries()
    {
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid));

        $response->assertStatus(302);
    }

    public function test_non_member_cannot_create_itinerary()
    {
        $nonMember = User::factory()->create();

        $response = $this->actingAs($nonMember)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan->uuid), [
                'title' => 'Test Itinerary',
                'date' => '2024-01-16',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('itineraries', ['title' => 'Test Itinerary']);
    }

    public function test_user_cannot_edit_others_itinerary()
    {
        $otherUser = User::factory()->create();
        Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $otherUser->id,
            'is_confirmed' => true,
        ]);

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.itineraries.update', [$this->travelPlan->uuid, $itinerary->id]), [
                'title' => 'Hacked Title',
                'date' => $itinerary->date->format('Y-m-d'),
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('itineraries', ['title' => 'Hacked Title']);
    }
}