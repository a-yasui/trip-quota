<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationControllerTest extends TestCase
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
            'departure_date' => '2024-07-01',
            'return_date' => '2024-07-05',
        ]);
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_index_displays_accommodations()
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'テストホテル',
            'check_in_date' => '2024-07-01',
            'check_out_date' => '2024-07-02',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('宿泊施設管理');
        $response->assertSee('テストホテル');
        $response->assertSee('2024/07/01');
        $response->assertSee('2024/07/02');
    }

    public function test_index_shows_empty_state_when_no_accommodations()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('宿泊施設がありません');
        $response->assertSee('最初の宿泊施設を追加してみましょう');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('宿泊施設を追加');
        $response->assertSee($this->travelPlan->plan_name);
        $response->assertSee('宿泊施設名');
        $response->assertSee('チェックイン日');
        $response->assertSee('チェックアウト日');
    }

    public function test_store_creates_accommodation_successfully()
    {
        $accommodationData = [
            'name' => '新しいホテル',
            'address' => '東京都渋谷区',
            'check_in_date' => '2024-07-01',
            'check_out_date' => '2024-07-02',
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'price_per_night' => 15000,
            'currency' => 'JPY',
            'confirmation_number' => 'CONF123',
            'notes' => 'テスト備考',
            'member_ids' => [$this->member->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), $accommodationData);

        $response->assertRedirect(route('travel-plans.accommodations.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success', '宿泊施設を追加しました。');

        $this->assertDatabaseHas('accommodations', [
            'travel_plan_id' => $this->travelPlan->id,
            'name' => '新しいホテル',
            'address' => '東京都渋谷区',
            'check_in_date' => '2024-07-01 00:00:00',
            'check_out_date' => '2024-07-02 00:00:00',
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), []);

        $response->assertSessionHasErrors(['name', 'check_in_date', 'check_out_date']);
    }

    public function test_store_validates_check_out_date_after_check_in_date()
    {
        $accommodationData = [
            'name' => 'テストホテル',
            'check_in_date' => '2024-07-03',
            'check_out_date' => '2024-07-01', // チェックイン日より前
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), $accommodationData);

        $response->assertSessionHasErrors(['check_out_date']);
    }

    public function test_show_displays_accommodation_details()
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'ホテル詳細テスト',
            'address' => '大阪府大阪市',
            'price_per_night' => 12000,
            'currency' => 'JPY',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.show', [$this->travelPlan->uuid, $accommodation->id]));

        $response->assertStatus(200);
        $response->assertSee('ホテル詳細テスト');
        $response->assertSee('大阪府大阪市');
        $response->assertSee('12,000 JPY');
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => '編集用ホテル',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.edit', [$this->travelPlan->uuid, $accommodation->id]));

        $response->assertStatus(200);
        $response->assertSee('宿泊施設を編集');
        $response->assertSee('編集用ホテル');
    }

    public function test_update_modifies_accommodation_successfully()
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => '元のホテル名',
        ]);

        $updateData = [
            'name' => '更新されたホテル名',
            'address' => '更新された住所',
            'check_in_date' => '2024-07-02',
            'check_out_date' => '2024-07-03',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.accommodations.update', [$this->travelPlan->uuid, $accommodation->id]), $updateData);

        $response->assertRedirect(route('travel-plans.accommodations.show', [$this->travelPlan->uuid, $accommodation->id]));
        $response->assertSessionHas('success', '宿泊施設を更新しました。');

        $this->assertDatabaseHas('accommodations', [
            'id' => $accommodation->id,
            'name' => '更新されたホテル名',
            'address' => '更新された住所',
        ]);
    }

    public function test_destroy_deletes_accommodation()
    {
        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.accommodations.destroy', [$this->travelPlan->uuid, $accommodation->id]));

        $response->assertRedirect(route('travel-plans.accommodations.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success', '宿泊施設を削除しました。');

        $this->assertDatabaseMissing('accommodations', [
            'id' => $accommodation->id,
        ]);
    }

    public function test_unauthorized_user_cannot_access_accommodations()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_accommodation_member_assignment()
    {
        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);

        $accommodationData = [
            'name' => 'メンバーテストホテル',
            'check_in_date' => '2024-07-01',
            'check_out_date' => '2024-07-02',
            'member_ids' => [$this->member->id, $member2->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), $accommodationData);

        $response->assertRedirect();

        $accommodation = Accommodation::where('name', 'メンバーテストホテル')->first();
        $this->assertNotNull($accommodation);

        // メンバーが正しく関連付けられているか確認
        $this->assertDatabaseHas('accommodation_members', [
            'accommodation_id' => $accommodation->id,
            'member_id' => $this->member->id,
        ]);

        $this->assertDatabaseHas('accommodation_members', [
            'accommodation_id' => $accommodation->id,
            'member_id' => $member2->id,
        ]);
    }
}
