<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 宿泊施設のタイムゾーン機能のテスト
 */
class AccommodationTimezoneTest extends TestCase
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
            'owner_user_id' => $this->user->id,
            'departure_date' => '2024-12-01',
            'return_date' => '2024-12-10',
        ]);
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    #[Test]
    public function can_create_accommodation_with_timezone(): void
    {
        $accommodationData = [
            'name' => 'Tokyo Hotel',
            'address' => 'Tokyo, Japan',
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-03',
            'check_in_time' => '15:00',
            'check_out_time' => '10:00',
            'timezone' => 'Asia/Tokyo',
            'price_per_night' => 10000,
            'currency' => 'JPY',
            'notes' => 'Test accommodation with timezone',
            'confirmation_number' => 'ABC123',
            'member_ids' => [$this->member->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), $accommodationData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accommodations', [
            'name' => 'Tokyo Hotel',
            'timezone' => 'Asia/Tokyo',
            'travel_plan_id' => $this->travelPlan->id,
        ]);
    }

    #[Test]
    public function timezone_field_is_optional(): void
    {
        $accommodationData = [
            'name' => 'Test Hotel',
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-02',
            'currency' => 'JPY',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), $accommodationData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accommodations', [
            'name' => 'Test Hotel',
            'timezone' => null,
        ]);
    }

    #[Test]
    public function can_update_accommodation_with_timezone(): void
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'timezone' => null,
        ]);

        $updateData = [
            'name' => $accommodation->name,
            'check_in_date' => '2024-12-01',
            'check_out_date' => '2024-12-03',
            'timezone' => 'Europe/London',
            'currency' => $accommodation->currency,
        ];

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.accommodations.update', [$this->travelPlan->uuid, $accommodation->id]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accommodations', [
            'id' => $accommodation->id,
            'timezone' => 'Europe/London',
        ]);
    }

    #[Test]
    public function edit_form_populates_timezone_field(): void
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'timezone' => 'Asia/Seoul',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.edit', [$this->travelPlan->uuid, $accommodation->id]));

        $response->assertOk();
        $response->assertSee('selected', false)
            ->assertSee('Asia/Seoul', false);
    }
}
