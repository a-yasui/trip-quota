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

class ItineraryValidationTest extends TestCase
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

    public function test_basic_validation_rules()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            []
        );

        $response->assertSessionHasErrors(['title', 'date']);
    }

    public function test_title_length_validation()
    {
        $longTitle = str_repeat('a', 256); // 255文字を超える

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => $longTitle,
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors('title');
    }

    public function test_date_within_travel_plan_validation()
    {
        // 旅行開始日より前の日付
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->subDay()->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors('date');

        // 旅行終了日より後の日付
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->return_date->addDay()->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors('date');
    }

    public function test_time_format_validation()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '25:00', // 無効な時刻
            ]
        );

        $response->assertSessionHasErrors('start_time');

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '09:30', // 正しい形式
            ]
        );

        $response->assertRedirect(); // 成功時はリダイレクト
        $response->assertSessionDoesntHaveErrors('start_time');
    }

    public function test_end_time_after_start_time_validation()
    {
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

        // 同じ時刻もエラー
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

    public function test_airplane_required_fields_validation()
    {
        // 飛行機を選択したが必須フィールドが未入力
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Flight Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'transportation_type' => 'airplane',
                // airline, flight_numberが未入力
            ]
        );

        $response->assertSessionHasErrors(['airline', 'flight_number']);

        // 必須フィールドを入力すれば成功
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Flight Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'transportation_type' => 'airplane',
                'airline' => 'JAL',
                'flight_number' => 'JL123',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_train_required_fields_validation()
    {
        // 電車を選択したが路線名が未入力
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Train Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'transportation_type' => 'train',
                // train_lineが未入力
            ]
        );

        $response->assertSessionHasErrors('train_line');

        // 路線名を入力すれば成功
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Train Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'transportation_type' => 'train',
                'train_line' => '東海道新幹線',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_time_conflict_detection_same_group()
    {
        $group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'Test Group',
        ]);

        // メンバーをグループに追加
        $group->members()->attach($this->member->id);

        // 既存の旅程を作成
        $existingItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'date' => $this->travelPlan->departure_date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'group_id' => $group->id,
        ]);

        // 同じグループで時間重複する旅程を作成しようとする
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Conflicting Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '13:00',
                'group_id' => $group->id,
            ]
        );

        $response->assertSessionHasErrors('start_time');
        $this->assertStringContainsString('時刻が重複しています', session('errors')->first('start_time'));
    }

    public function test_time_conflict_detection_same_members()
    {
        $member2 = Member::factory()->forTravelPlan($this->travelPlan)->create();

        // 既存の旅程を作成
        $existingItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'date' => $this->travelPlan->departure_date,
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);
        $existingItinerary->members()->attach([$this->member->id, $member2->id]);

        // 同じメンバーで時間重複する旅程を作成しようとする
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Conflicting Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '13:00',
                'member_ids' => [$this->member->id], // 重複するメンバー
            ]
        );

        $response->assertSessionHasErrors('start_time');
        $this->assertStringContainsString('時刻が重複しています', session('errors')->first('start_time'));
    }

    public function test_no_time_conflict_different_groups_and_members()
    {
        $group1 = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'Group 1',
        ]);

        $group2 = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'Group 2',
        ]);

        $member2 = Member::factory()->forTravelPlan($this->travelPlan)->create();

        // メンバーをそれぞれのグループに追加
        $group1->members()->attach($this->member->id);
        $group2->members()->attach($this->member->id); // 作成者は両方のグループに属させる

        // 既存の旅程を作成
        $existingItinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'date' => $this->travelPlan->departure_date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'group_id' => $group1->id,
        ]);
        $existingItinerary->members()->attach([$this->member->id]);

        // 別のグループ・別のメンバーで時間重複する旅程を作成（成功するはず）
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Non-conflicting Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '11:00',
                'end_time' => '13:00',
                'group_id' => $group2->id,
                'member_ids' => [$member2->id],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_group_belongs_to_travel_plan_validation()
    {
        $otherTravelPlan = TravelPlan::factory()->create();
        $otherGroup = Group::factory()->create([
            'travel_plan_id' => $otherTravelPlan->id,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'group_id' => $otherGroup->id, // 他の旅行プランのグループ
            ]
        );

        $response->assertSessionHasErrors('group_id');
    }

    public function test_member_belongs_to_travel_plan_validation()
    {
        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'member_ids' => [99999], // 存在しないメンバーID
            ]
        );

        // member_ids.* のバリデーションエラーをチェック
        $response->assertSessionHasErrors();
    }

    public function test_flight_duration_validation()
    {
        $departureTime = Carbon::now()->addDays(1)->setTime(10, 0);
        $arrivalTime = $departureTime->copy()->addHours(25); // 25時間後（無効）

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Long Flight',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'transportation_type' => 'airplane',
                'airline' => 'Test Airline',
                'flight_number' => 'TA123',
                'departure_time' => $departureTime->format('Y-m-d H:i:s'),
                'arrival_time' => $arrivalTime->format('Y-m-d H:i:s'),
            ]
        );

        $response->assertSessionHasErrors('arrival_time');

        // 短すぎるフライト（15分）
        $shortArrival = $departureTime->copy()->addMinutes(15);

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Short Flight',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'transportation_type' => 'airplane',
                'airline' => 'Test Airline',
                'flight_number' => 'TA124',
                'departure_time' => $departureTime->format('Y-m-d H:i:s'),
                'arrival_time' => $shortArrival->format('Y-m-d H:i:s'),
            ]
        );

        $response->assertSessionHasErrors('arrival_time');
    }

    public function test_update_validation_excludes_current_itinerary_from_conflict_check()
    {
        // 既存の旅程を作成
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by_member_id' => $this->member->id,
            'date' => $this->travelPlan->departure_date,
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        // 同じ時刻で更新（自分自身との重複チェックが除外されるべき）
        $response = $this->actingAs($this->user)->put(
            route('travel-plans.itineraries.update', [$this->travelPlan->uuid, $itinerary->id]),
            [
                'title' => 'Updated Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_character_limits_validation()
    {
        $longText = str_repeat('a', 2001); // 2000文字を超える

        $response = $this->actingAs($this->user)->post(
            route('travel-plans.itineraries.store', $this->travelPlan->uuid),
            [
                'title' => 'Test Itinerary',
                'date' => $this->travelPlan->departure_date->format('Y-m-d'),
                'description' => $longText,
                'notes' => $longText,
            ]
        );

        $response->assertSessionHasErrors(['description', 'notes']);
    }
}
