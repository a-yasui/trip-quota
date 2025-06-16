<?php

namespace Tests\Unit\TripQuota;

use App\Models\Group;
use App\Models\Itinerary;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use TripQuota\Itinerary\ItineraryRepositoryInterface;
use TripQuota\Itinerary\ItineraryService;

class ItineraryServiceTest extends TestCase
{
    use RefreshDatabase;

    private ItineraryService $service;

    private ItineraryRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // モック用のリポジトリを作成
        $this->repository = $this->createMock(ItineraryRepositoryInterface::class);
        $this->service = new ItineraryService($this->repository);
    }

    public function test_get_itineraries_by_travel_plan_returns_itineraries_for_confirmed_member()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $member = Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $expectedItineraries = new Collection([
            new Itinerary(['title' => 'Test Itinerary']),
        ]);

        $this->repository->expects($this->once())
            ->method('findByTravelPlan')
            ->with($travelPlan)
            ->willReturn($expectedItineraries);

        $result = $this->service->getItinerariesByTravelPlan($travelPlan, $user);

        $this->assertEquals($expectedItineraries, $result);
    }

    public function test_get_itineraries_by_travel_plan_throws_exception_for_non_member()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('この旅行プランの旅程を閲覧する権限がありません。');

        $this->service->getItinerariesByTravelPlan($travelPlan, $user);
    }

    public function test_get_itineraries_by_travel_plan_throws_exception_for_unconfirmed_member()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();

        // 未確認メンバーを作成
        $member = Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();
        $member->update(['is_confirmed' => false]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('この旅行プランの旅程を閲覧する権限がありません。');

        $this->service->getItinerariesByTravelPlan($travelPlan, $user);
    }

    public function test_get_itineraries_by_group_returns_group_itineraries()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $group = Group::factory()->for($travelPlan)->create();
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $expectedItineraries = new Collection([
            new Itinerary(['title' => 'Group Itinerary']),
        ]);

        $this->repository->expects($this->once())
            ->method('findByTravelPlanAndGroup')
            ->with($travelPlan, $group)
            ->willReturn($expectedItineraries);

        $result = $this->service->getItinerariesByGroup($travelPlan, $group, $user);

        $this->assertEquals($expectedItineraries, $result);
    }

    public function test_get_itineraries_by_date_returns_date_specific_itineraries()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $date = Carbon::parse('2024-01-15');
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $expectedItineraries = new Collection([
            new Itinerary(['title' => 'Date Itinerary']),
        ]);

        $this->repository->expects($this->once())
            ->method('findByTravelPlanAndDate')
            ->with($travelPlan, $date)
            ->willReturn($expectedItineraries);

        $result = $this->service->getItinerariesByDate($travelPlan, $date, $user);

        $this->assertEquals($expectedItineraries, $result);
    }

    public function test_get_itineraries_by_date_range_returns_range_itineraries()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $startDate = Carbon::parse('2024-01-15');
        $endDate = Carbon::parse('2024-01-20');
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $expectedItineraries = new Collection([
            new Itinerary(['title' => 'Range Itinerary']),
        ]);

        $this->repository->expects($this->once())
            ->method('findByTravelPlanDateRange')
            ->with($travelPlan, $startDate, $endDate)
            ->willReturn($expectedItineraries);

        $result = $this->service->getItinerariesByDateRange($travelPlan, $startDate, $endDate, $user);

        $this->assertEquals($expectedItineraries, $result);
    }

    public function test_create_itinerary_validates_required_fields()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $this->expectException(ValidationException::class);

        $this->service->createItinerary($travelPlan, $user, []);
    }

    public function test_create_itinerary_validates_end_time_after_start_time()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $invalidData = [
            'title' => 'Test Itinerary',
            'date' => '2024-01-15',
            'start_time' => '14:00',
            'end_time' => '13:00', // 開始時刻より前
        ];

        $this->expectException(ValidationException::class);

        $this->service->createItinerary($travelPlan, $user, $invalidData);
    }

    public function test_create_itinerary_validates_airplane_fields()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $invalidData = [
            'title' => 'Flight Itinerary',
            'date' => '2024-01-15',
            'transportation_type' => 'airplane',
            // flight_numberが必要だが未指定
        ];

        $this->expectException(ValidationException::class);

        $this->service->createItinerary($travelPlan, $user, $invalidData);
    }

    public function test_create_itinerary_validates_date_within_travel_period()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create([
            'departure_date' => Carbon::parse('2024-01-15'),
            'return_date' => Carbon::parse('2024-01-20'),
        ]);
        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $invalidData = [
            'title' => 'Test Itinerary',
            'date' => '2024-01-25', // 旅行期間外
        ];

        $this->expectException(ValidationException::class);

        $this->service->createItinerary($travelPlan, $user, $invalidData);
    }

    public function test_create_itinerary_validates_group_belongs_to_travel_plan()
    {
        $user = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $otherTravelPlan = TravelPlan::factory()->create();
        $group = Group::factory()->for($otherTravelPlan)->create();

        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $invalidData = [
            'title' => 'Test Itinerary',
            'date' => '2024-01-15',
            'group_id' => $group->id, // 異なる旅行プランのグループ
        ];

        $this->expectException(ValidationException::class);

        $this->service->createItinerary($travelPlan, $user, $invalidData);
    }

    public function test_update_itinerary_validates_user_permissions()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $otherMember = Member::factory()->forUser($otherUser)->forTravelPlan($travelPlan)->create();

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'created_by_member_id' => $otherMember->id,
        ]);

        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('この旅程を編集できるのは作成者または旅行プラン管理者のみです。');

        $this->service->updateItinerary($itinerary, $user, ['title' => 'Updated']);
    }

    public function test_delete_itinerary_validates_user_permissions()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $travelPlan = TravelPlan::factory()->create();
        $otherMember = Member::factory()->forUser($otherUser)->forTravelPlan($travelPlan)->create();

        $itinerary = Itinerary::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'created_by_member_id' => $otherMember->id,
        ]);

        Member::factory()->forUser($user)->forTravelPlan($travelPlan)->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('この旅程を編集できるのは作成者または旅行プラン管理者のみです。');

        $this->service->deleteItinerary($itinerary, $user);
    }
}
