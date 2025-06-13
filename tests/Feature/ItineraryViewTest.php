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

class ItineraryViewTest extends TestCase
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
            'plan_name' => 'Test Travel Plan',
            'departure_date' => Carbon::parse('2024-01-15'),
            'return_date' => Carbon::parse('2024-01-20'),
        ]);
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_index_view_displays_correctly()
    {
        $group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Group',
            'group_type' => 'CORE',
        ]);

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Sample Itinerary',
            'description' => 'Test description',
            'group_id' => $group->id,
            'transportation_type' => 'airplane',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程管理 - Test Travel Plan');
        $response->assertSee('旅程管理');
        $response->assertSee('Test Travel Plan');
        $response->assertSee('旅程を追加');
        $response->assertSee('タイムライン表示');
        $response->assertSee('Sample Itinerary');
        $response->assertSee('Test Group');
        $response->assertSee('グループでフィルター');
        $response->assertSee('日付でフィルター');
    }

    public function test_index_view_shows_empty_state_when_no_itineraries()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程がありません');
        $response->assertSee('最初の旅程を作成して、旅行スケジュールを管理しましょう。');
        $response->assertSee('旅程を追加');
    }

    public function test_create_view_displays_form_correctly()
    {
        $group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Group',
            'group_type' => 'BRANCH',
        ]);

        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Member',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程作成 - Test Travel Plan');
        $response->assertSee('旅程作成');
        $response->assertSee('Test Travel Planの新しい旅程を作成します。');
        $response->assertSee('タイトル');
        $response->assertSee('説明');
        $response->assertSee('日付');
        $response->assertSee('開始時刻');
        $response->assertSee('終了時刻');
        $response->assertSee('対象グループ');
        $response->assertSee('[班] Test Group');
        $response->assertSee('交通手段');
        $response->assertSee('参加者');
        $response->assertSee('Test Member');
        $response->assertSee('旅程を作成');
        $response->assertSee('キャンセル');
    }

    public function test_create_view_shows_transportation_options()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('徒歩');
        $response->assertSee('自転車');
        $response->assertSee('車');
        $response->assertSee('バス');
        $response->assertSee('フェリー');
        $response->assertSee('飛行機');
        $response->assertSee('航空会社');
        $response->assertSee('便名');
    }

    public function test_show_view_displays_itinerary_details()
    {
        $group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Sample Group',
            'group_type' => 'CORE',
        ]);

        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Sample Member',
            'is_confirmed' => true,
        ]);

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Tokyo to Osaka Flight',
            'description' => 'Morning flight to Osaka',
            'date' => Carbon::parse('2024-01-16'),
            'start_time' => Carbon::parse('09:00'),
            'end_time' => Carbon::parse('10:30'),
            'transportation_type' => 'airplane',
            'airline' => 'JAL',
            'flight_number' => 'JL123',
            'departure_location' => 'Haneda Airport',
            'arrival_location' => 'Kansai Airport',
            'notes' => 'Check-in 2 hours early',
            'group_id' => $group->id,
            'created_by' => $this->user->id,
        ]);

        $itinerary->members()->attach($member);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.show', [$this->travelPlan->uuid, $itinerary->id]));

        $response->assertStatus(200);
        $response->assertSee('Tokyo to Osaka Flight - Test Travel Plan');
        $response->assertSee('Tokyo to Osaka Flight');
        $response->assertSee('Morning flight to Osaka');
        $response->assertSee('2024年1月16日');
        $response->assertSee('09:00');
        $response->assertSee('10:30');
        $response->assertSee('[全体] Sample Group');
        $response->assertSee('✈️ 飛行機');
        $response->assertSee('JAL');
        $response->assertSee('JL123');
        $response->assertSee('Haneda Airport');
        $response->assertSee('Kansai Airport');
        $response->assertSee('Check-in 2 hours early');
        $response->assertSee('Sample Member');
        $response->assertSee('編集');
        $response->assertSee('削除');
        $response->assertSee('旅程を編集');
        $response->assertSee('同じ条件で新規作成');
    }

    public function test_edit_view_displays_form_with_existing_data()
    {
        $group = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Edit Group',
        ]);

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Original Title',
            'description' => 'Original description',
            'transportation_type' => 'car',
            'departure_location' => 'Tokyo Station',
            'group_id' => $group->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.edit', [$this->travelPlan->uuid, $itinerary->id]));

        $response->assertStatus(200);
        $response->assertSee('旅程編集 - Test Travel Plan');
        $response->assertSee('旅程編集');
        $response->assertSee('Test Travel Planの旅程を編集します。');
        $response->assertSee('value="Original Title"', false);
        $response->assertSee('Original description');
        $response->assertSee('value="Tokyo Station"', false);
        $response->assertSee('変更を保存');
        $response->assertSee('キャンセル');
    }

    public function test_timeline_view_displays_correctly()
    {
        $itinerary1 = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Morning Activity',
            'date' => Carbon::parse('2024-01-16'),
            'start_time' => Carbon::parse('09:00'),
            'transportation_type' => 'walking',
            'created_by' => $this->user->id,
        ]);

        $itinerary2 = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Afternoon Activity',
            'date' => Carbon::parse('2024-01-16'),
            'start_time' => Carbon::parse('14:00'),
            'transportation_type' => 'bus',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('旅程タイムライン - Test Travel Plan');
        $response->assertSee('旅程タイムライン');
        $response->assertSee('Test Travel Planのスケジュール');
        $response->assertSee('リスト表示');
        $response->assertSee('旅程を追加');
        $response->assertSee('1月16日');
        $response->assertSee('2件の旅程');
        $response->assertSee('Morning Activity');
        $response->assertSee('Afternoon Activity');
        $response->assertSee('09:00');
        $response->assertSee('14:00');
        $response->assertSee('🚶');
        $response->assertSee('🚌');
        $response->assertSee('この日に追加');
    }

    public function test_timeline_view_shows_empty_state()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('指定期間に旅程がありません');
        $response->assertSee('2024年1月15日 から 2024年1月20日 の期間に旅程がありません。');
        $response->assertSee('旅程を追加');
    }

    public function test_timeline_view_shows_date_filter_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('開始日');
        $response->assertSee('終了日');
        $response->assertSee('期間変更');
        $response->assertSee('リセット');
        $response->assertSee('value="2024-01-15"', false);
        $response->assertSee('value="2024-01-20"', false);
    }

    public function test_views_show_proper_navigation_links()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'created_by' => $this->user->id,
        ]);

        // Test index navigation
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.index', $this->travelPlan->uuid));
        
        $response->assertSee('旅行プラン詳細に戻る');
        $response->assertSee('グループ管理');
        $response->assertSee('メンバー管理');

        // Test show navigation
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.show', [$this->travelPlan->uuid, $itinerary->id]));
        
        $response->assertSee('旅程一覧に戻る');
        $response->assertSee('タイムライン表示');
        $response->assertSee('旅行プラン詳細');

        // Test timeline navigation
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid));
        
        $response->assertSee('旅行プラン詳細に戻る');
        $response->assertSee('グループ管理');
        $response->assertSee('メンバー管理');
    }

    public function test_views_display_correct_transportation_icons()
    {
        $transportationTypes = [
            'airplane' => '✈️',
            'car' => '🚗',
            'bus' => '🚌',
            'ferry' => '⛴️',
            'bike' => '🚲',
            'walking' => '🚶',
        ];

        foreach ($transportationTypes as $type => $icon) {
            $itinerary = Itinerary::factory()->create([
                'travel_plan_id' => $this->travelPlan->id,
                'title' => "Test {$type}",
                'transportation_type' => $type,
                'created_by' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('travel-plans.itineraries.timeline', $this->travelPlan->uuid));

            $response->assertSee($icon);
        }
    }

    public function test_views_handle_missing_optional_fields_gracefully()
    {
        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'title' => 'Minimal Itinerary',
            'date' => Carbon::parse('2024-01-16'),
            'description' => null,
            'start_time' => null,
            'end_time' => null,
            'transportation_type' => null,
            'group_id' => null,
            'notes' => null,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.itineraries.show', [$this->travelPlan->uuid, $itinerary->id]));

        $response->assertStatus(200);
        $response->assertSee('時間未指定');
        $response->assertSee('すべてのメンバー');
        $response->assertDontSee('説明');
        $response->assertDontSee('メモ');
    }
}