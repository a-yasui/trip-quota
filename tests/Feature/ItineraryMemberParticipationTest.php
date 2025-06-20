<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\Itinerary\ItineraryRepository;
use TripQuota\Itinerary\ItineraryService;

class ItineraryMemberParticipationTest extends TestCase
{
    use RefreshDatabase;

    private ItineraryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ItineraryService(new ItineraryRepository);
    }

    public function test_get_member_participation_stats_returns_correct_statistics()
    {
        // テストデータの準備
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();

        // メンバーを作成
        $member1 = Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create(['name' => 'Member 1']);
        $user2 = User::factory()->create();
        $member2 = Member::factory()->forUser($user2)->forTravelPlan($travelPlan)->create(['name' => 'Member 2']);
        $user3 = User::factory()->create();
        $member3 = Member::factory()->forUser($user3)->forTravelPlan($travelPlan)->create(['name' => 'Member 3']);

        // 旅程を作成
        $itinerary1 = Itinerary::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'created_by_member_id' => $member1->id,
            'title' => 'Itinerary 1',
            'date' => $travelPlan->departure_date,
        ]);
        $itinerary1->members()->attach([$member1->id, $member2->id]); // Member1, Member2が参加

        $itinerary2 = Itinerary::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'created_by_member_id' => $member1->id,
            'title' => 'Itinerary 2',
            'date' => $travelPlan->departure_date,
        ]);
        $itinerary2->members()->attach([$member1->id]); // Member1のみ参加

        $itinerary3 = Itinerary::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'created_by_member_id' => $member1->id,
            'title' => 'Itinerary 3',
            'date' => $travelPlan->departure_date,
        ]);
        $itinerary3->members()->attach([$member1->id, $member2->id, $member3->id]); // 全員参加

        // メンバー参加統計を取得
        $result = $this->service->getMemberParticipationStats($travelPlan, $user);

        // 検証
        $this->assertEquals(3, $result['total_members']);
        $this->assertEquals(3, $result['total_itineraries']);
        $this->assertCount(3, $result['member_stats']);

        // 参加率でソートされているかチェック（Member1が最も高い参加率）
        $firstMember = $result['member_stats'][0];
        $this->assertEquals('Member 1', $firstMember['member']->name);
        $this->assertEquals(3, $firstMember['participation_count']);
        $this->assertEquals(100.0, $firstMember['participation_rate']);

        // Member2の統計確認
        $member2Stats = collect($result['member_stats'])->firstWhere('member.name', 'Member 2');
        $this->assertEquals(2, $member2Stats['participation_count']);
        $this->assertEquals(66.7, $member2Stats['participation_rate']);

        // Member3の統計確認
        $member3Stats = collect($result['member_stats'])->firstWhere('member.name', 'Member 3');
        $this->assertEquals(1, $member3Stats['participation_count']);
        $this->assertEquals(33.3, $member3Stats['participation_rate']);
    }

    public function test_get_member_participation_stats_with_no_itineraries()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create(['name' => 'Member 1']);

        $result = $this->service->getMemberParticipationStats($travelPlan, $user);

        $this->assertEquals(1, $result['total_members']);
        $this->assertEquals(0, $result['total_itineraries']);
        $this->assertCount(1, $result['member_stats']);

        // 旅程がないときは参加率は0%
        $memberStats = $result['member_stats'][0];
        $this->assertEquals(0, $memberStats['participation_count']);
        $this->assertEquals(0, $memberStats['participation_rate']);
    }

    public function test_get_member_participation_stats_requires_valid_user_permissions()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        // メンバーを作成しない = 権限なし

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('この旅行プランの旅程を閲覧する権限がありません。');

        $this->service->getMemberParticipationStats($travelPlan, $user);
    }

    public function test_enhanced_member_selection_shows_group_information()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();

        // グループを作成
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'CORE',
            'name' => 'Core Group',
        ]);

        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'BRANCH',
            'name' => 'Branch Group A',
        ]);

        // メンバーを作成してグループに関連付け
        $member1 = Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create(['name' => 'Member 1']);
        $member1->groups()->attach([$coreGroup->id, $branchGroup->id]);

        $user2 = User::factory()->create();
        $member2 = Member::factory()->forUser($user2)->forTravelPlan($travelPlan)->create(['name' => 'Member 2']);
        $member2->groups()->attach([$coreGroup->id]);

        // create画面にアクセス
        $response = $this->actingAs($user)->get(route('travel-plans.itineraries.create', $travelPlan->uuid));

        $response->assertOk();
        $response->assertSee('Member 1');
        $response->assertSee('Member 2');
        $response->assertSee('全体'); // コアグループの表示
        $response->assertSee('Branch Group A'); // 班グループの表示
        $response->assertSee('確認済み'); // メンバー状態表示
        $response->assertSee('全選択'); // 全選択ボタン
        $response->assertSee('全解除'); // 全解除ボタン
    }

    public function test_enhanced_member_display_in_show_view()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();

        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => 'CORE',
            'name' => 'Core Group',
        ]);

        $member1 = Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create(['name' => 'Member 1']);
        $member1->groups()->attach([$coreGroup->id]);

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'created_by_member_id' => $member1->id,
            'title' => 'Test Itinerary',
            'date' => $travelPlan->departure_date,
        ]);
        $itinerary->members()->attach([$member1->id]);

        // show画面にアクセス
        $response = $this->actingAs($user)->get(route('travel-plans.itineraries.show', [$travelPlan->uuid, $itinerary->id]));

        $response->assertOk();
        $response->assertSee('Member 1');
        $response->assertSee('全体'); // グループ情報表示
        $response->assertSee('確認済み'); // メンバー状態表示
        $response->assertSee('作成者'); // 作成者バッジ表示
    }
}
