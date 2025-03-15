<?php

namespace Tests\Unit\TripQuota\User;

use App\Enums\GroupType;
use App\Models\ExpenseSettlement;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\User\UserService;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    /**
     * @test
     */
    public function createMember_メンバーを作成できる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);

        $name = 'テストメンバー';
        $email = 'test@example.com';

        // 実行
        $member = $this->userService->createMember($travelPlan, $name, $email);

        // 検証
        $this->assertNotNull($member);
        $this->assertEquals($name, $member->name);
        $this->assertEquals($email, $member->email);
        $this->assertEquals($coreGroup->id, $member->group_id);
        $this->assertTrue($member->is_active);
        $this->assertNull($member->user_id); // ユーザーが存在しない場合
    }

    /**
     * @test
     */
    public function createMember_既存のユーザーと関連付けられる()
    {
        // 準備
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);

        $name = '既存ユーザー';
        $email = 'existing@example.com';

        // 実行
        $member = $this->userService->createMember($travelPlan, $name, $email);

        // 検証
        $this->assertNotNull($member);
        $this->assertEquals($name, $member->name);
        $this->assertEquals($email, $member->email);
        $this->assertEquals($coreGroup->id, $member->group_id);
        $this->assertEquals($user->id, $member->user_id);
        $this->assertTrue($member->is_active);
    }

    /**
     * @test
     */
    public function createMember_既存のメンバーが存在する場合は何もしない()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $existingMember = Member::factory()->create([
            'name' => '既存メンバー',
            'email' => 'existing-member@example.com',
            'group_id' => $coreGroup->id,
        ]);

        // 実行
        $member = $this->userService->createMember(
            $travelPlan,
            '新しい名前',
            'existing-member@example.com'
        );

        // 検証
        $this->assertNotNull($member);
        $this->assertEquals($existingMember->id, $member->id);
        $this->assertEquals('既存メンバー', $member->name); // 名前は更新されない
        $this->assertEquals('existing-member@example.com', $member->email);
    }

    /**
     * @test
     */
    public function removeMember_メンバーを削除できる_班グループなし()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        // コアグループに別のメンバーも追加（最後のメンバーを削除できない対策）
        $anotherMember = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);

        // 実行
        $this->userService->removeMember($travelPlan, $member);

        // 検証
        $this->assertFalse($member->fresh()->is_active);
    }

    /**
     * @test
     */
    public function removeMember_メンバーが旅行計画に所属していない場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $otherTravelPlan = TravelPlan::factory()->create();
        $otherCoreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $otherTravelPlan->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $otherCoreGroup->id,
        ]);

        // 期待値
        $this->expectException(ModelNotFoundException::class);

        // 実行
        $this->userService->removeMember($travelPlan, $member);
    }

    /**
     * @test
     */
    public function removeMember_精算データがある場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $anotherMember = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);

        // 精算データを作成
        ExpenseSettlement::factory()->create([
            'payer_member_id' => $member->id,
            'receiver_member_id' => $anotherMember->id,
            'travel_plan_id' => $travelPlan->id,
            'amount' => 1000,
            'currency' => 'JPY',
            'is_settled' => false,
        ]);

        // 期待値
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('メンバーに精算データがあるため削除できません。');

        // 実行
        $this->userService->removeMember($travelPlan, $member);
    }
} 