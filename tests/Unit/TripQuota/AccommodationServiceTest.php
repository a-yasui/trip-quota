<?php

namespace Tests\Unit\TripQuota;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\TravelPlan;
use App\Models\User;
use App\Models\Member;
use App\Models\Accommodation;
use TripQuota\Accommodation\AccommodationService;
use TripQuota\Accommodation\AccommodationRepositoryInterface;
use TripQuota\Member\MemberRepositoryInterface;

class AccommodationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AccommodationService $service;
    private AccommodationRepositoryInterface $accommodationRepository;
    private MemberRepositoryInterface $memberRepository;
    private User $user;
    private TravelPlan $travelPlan;
    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->accommodationRepository = $this->createMock(AccommodationRepositoryInterface::class);
        $this->memberRepository = $this->createMock(MemberRepositoryInterface::class);
        $this->service = new AccommodationService($this->accommodationRepository, $this->memberRepository);
        
        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
            'departure_date' => '2024-07-01',
            'return_date' => '2024-07-05',
        ]);
        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_create_accommodation_successfully()
    {
        $accommodationData = [
            'name' => 'ホテルテスト',
            'address' => '東京都渋谷区',
            'check_in_date' => '2024-07-01',
            'check_out_date' => '2024-07-03',
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'price_per_night' => 10000,
            'currency' => 'JPY',
            'confirmation_number' => 'ABC123',
            'notes' => 'テストメモ',
        ];

        $expectedAccommodation = Accommodation::factory()->make([
            'id' => 1,
            'name' => 'ホテルテスト',
        ]);

        $this->memberRepository
            ->expects($this->exactly(2))
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->accommodationRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn($expectedAccommodation);

        $result = $this->service->createAccommodation($this->travelPlan, $this->user, $accommodationData);

        $this->assertEquals($expectedAccommodation, $result);
    }

    public function test_create_accommodation_fails_with_invalid_dates()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('チェックアウト日はチェックイン日より後の日付である必要があります。');

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $accommodationData = [
            'name' => 'ホテルテスト',
            'check_in_date' => '2024-07-03',
            'check_out_date' => '2024-07-01', // チェックイン日より前
        ];

        $this->service->createAccommodation($this->travelPlan, $this->user, $accommodationData);
    }

    public function test_create_accommodation_fails_when_dates_outside_travel_period()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('チェックイン日は旅行開始日以降である必要があります。');

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $accommodationData = [
            'name' => 'ホテルテスト',
            'check_in_date' => '2024-06-30', // 旅行期間外
            'check_out_date' => '2024-07-02',
        ];

        $this->service->createAccommodation($this->travelPlan, $this->user, $accommodationData);
    }

    public function test_get_accommodations_for_travel_plan()
    {
        $accommodation1 = Accommodation::factory()->make(['id' => 1, 'name' => 'ホテルA']);
        $accommodation2 = Accommodation::factory()->make(['id' => 2, 'name' => 'ホテルB']);
        $expectedAccommodations = new \Illuminate\Database\Eloquent\Collection([
            $accommodation1, $accommodation2
        ]);

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->accommodationRepository
            ->expects($this->once())
            ->method('findByTravelPlan')
            ->with($this->travelPlan)
            ->willReturn($expectedAccommodations);

        $result = $this->service->getAccommodationsForTravelPlan($this->travelPlan, $this->user);

        $this->assertEquals($expectedAccommodations, $result);
    }

    public function test_delete_accommodation()
    {
        $accommodation = Accommodation::factory()->make([
            'id' => 1,
        ]);
        $accommodation->travelPlan = $this->travelPlan;

        $this->memberRepository
            ->expects($this->once())
            ->method('findByTravelPlanAndUser')
            ->with($this->travelPlan, $this->user)
            ->willReturn($this->member);

        $this->accommodationRepository
            ->expects($this->once())
            ->method('delete')
            ->with($accommodation)
            ->willReturn(true);

        $result = $this->service->deleteAccommodation($accommodation, $this->user);

        $this->assertTrue($result);
    }
}