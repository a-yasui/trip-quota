<?php

namespace Tests\Feature;

use App\Enums\GroupType;
use App\Enums\Transportation;
use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItineraryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $travelPlan;
    protected $coreGroup;

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザーを作成
        $this->user = User::factory()->create();

        // 旅行計画を作成
        $this->travelPlan = TravelPlan::factory()->create([
            'creator_id' => $this->user->id,
            'deletion_permission_holder_id' => $this->user->id,
        ]);

        // コアグループを作成
        $this->coreGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // メンバーを作成
        Member::factory()->create([
            'user_id' => $this->user->id,
            'group_id' => $this->coreGroup->id,
            'is_registered' => true,
        ]);
    }

    /**
     * 旅程一覧ページのテスト
     */
    public function test_itinerary_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan));

        $response->assertStatus(200);
        $response->assertViewIs('itineraries.index');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('itineraries');
    }

    /**
     * 旅程作成ページのテスト
     */
    public function test_itinerary_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.create', $this->travelPlan));

        $response->assertStatus(200);
        $response->assertViewIs('itineraries.create');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('members');
        $response->assertViewHas('transportationTypes');
    }

    /**
     * 旅程の作成テスト
     */
    public function test_itinerary_can_be_created(): void
    {
        $data = [
            'transportation_type' => Transportation::TRAIN->value,
            'departure_location' => '東京駅',
            'arrival_location' => '大阪駅',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'arrival_time' => now()->addDay()->addHours(3)->format('Y-m-d\TH:i'),
            'company_name' => 'JR東海',
            'reference_number' => 'のぞみ203号',
            'notes' => 'テスト旅程',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan), $data);

        $response->assertRedirect(route('travel-plans.itineraries.index', $this->travelPlan));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('itineraries', [
            'travel_plan_id' => $this->travelPlan->id,
            'transportation_type' => Transportation::TRAIN->value,
            'departure_location' => '東京駅',
            'arrival_location' => '大阪駅',
            'company_name' => 'JR東海',
            'reference_number' => 'のぞみ203号',
            'notes' => 'テスト旅程',
        ]);
    }

    /**
     * 飛行機の場合は会社名と便名が必須のテスト
     */
    public function test_flight_requires_company_name_and_reference_number(): void
    {
        $data = [
            'transportation_type' => Transportation::FLIGHT->value,
            'departure_location' => '羽田空港',
            'arrival_location' => '福岡空港',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'arrival_time' => now()->addDay()->addHours(2)->format('Y-m-d\TH:i'),
            'notes' => 'テスト旅程',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan), $data);

        $response->assertSessionHasErrors(['company_name', 'reference_number']);
    }

    /**
     * 旅程詳細ページのテスト
     */
    public function test_itinerary_show_page_can_be_rendered(): void
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.show', [$this->travelPlan, $itinerary]));

        $response->assertStatus(200);
        $response->assertViewIs('itineraries.show');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('itinerary');
    }

    /**
     * 旅程編集ページのテスト
     */
    public function test_itinerary_edit_page_can_be_rendered(): void
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.edit', [$this->travelPlan, $itinerary]));

        $response->assertStatus(200);
        $response->assertViewIs('itineraries.edit');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('itinerary');
        $response->assertViewHas('members');
        $response->assertViewHas('selectedMemberIds');
        $response->assertViewHas('transportationTypes');
    }

    /**
     * 旅程の更新テスト
     */
    public function test_itinerary_can_be_updated(): void
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'transportation_type' => Transportation::TRAIN->value,
        ]);

        $data = [
            'transportation_type' => Transportation::BUS->value,
            'departure_location' => '新宿駅',
            'arrival_location' => '東京ディズニーランド',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'arrival_time' => now()->addDay()->addHours(2)->format('Y-m-d\TH:i'),
            'company_name' => '東京バス',
            'reference_number' => 'ディズニー行き',
            'notes' => '更新テスト',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.itineraries.update', [$this->travelPlan, $itinerary]), $data);

        $response->assertRedirect(route('travel-plans.itineraries.index', $this->travelPlan));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('itineraries', [
            'id' => $itinerary->id,
            'transportation_type' => Transportation::BUS->value,
            'departure_location' => '新宿駅',
            'arrival_location' => '東京ディズニーランド',
            'company_name' => '東京バス',
            'reference_number' => 'ディズニー行き',
            'notes' => '更新テスト',
        ]);
    }

    /**
     * 旅程の削除テスト
     */
    public function test_itinerary_can_be_deleted(): void
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.itineraries.destroy', [$this->travelPlan, $itinerary]));

        $response->assertRedirect(route('travel-plans.itineraries.index', $this->travelPlan));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('itineraries', [
            'id' => $itinerary->id,
        ]);
    }

    /**
     * 旅程にメンバーを関連付けるテスト
     */
    public function test_members_can_be_attached_to_itinerary(): void
    {
        $members = Member::factory()->count(3)->create([
            'group_id' => $this->coreGroup->id,
        ]);

        $data = [
            'transportation_type' => Transportation::TRAIN->value,
            'departure_location' => '東京駅',
            'arrival_location' => '大阪駅',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'arrival_time' => now()->addDay()->addHours(3)->format('Y-m-d\TH:i'),
            'company_name' => 'JR東海',
            'reference_number' => 'のぞみ203号',
            'notes' => 'テスト旅程',
            'member_ids' => $members->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.itineraries.store', $this->travelPlan), $data);

        $response->assertRedirect(route('travel-plans.itineraries.index', $this->travelPlan));
        $response->assertSessionHas('success');

        $itinerary = Itinerary::latest('id')->first();

        foreach ($members as $member) {
            $this->assertDatabaseHas('itinerary_member', [
                'itinerary_id' => $itinerary->id,
                'member_id' => $member->id,
            ]);
        }
    }
}
