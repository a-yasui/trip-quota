<?php

namespace Tests\Unit\TripQuota\Group;

use App\Enums\GroupType;
use App\Models\ExpenseSettlement;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\Group\GroupService;

class GroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private GroupService $groupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupService = new GroupService();
    }

    /**
     * @test
     */
    public function addCoreMember_既にメンバーがコアグループに所属している場合は何もしない()
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

        // 実行
        $result = $this->groupService->addCoreMember($travelPlan, $member);

        // 検証
        $this->assertEquals($coreGroup->id, $result->id);
        $this->assertEquals($coreGroup->id, $member->fresh()->group_id);
    }

    /**
     * @test
     */
    public function addCoreMember_メンバーをコアグループに追加できる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $otherGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => TravelPlan::factory()->create()->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $otherGroup->id,
        ]);

        // 実行
        $result = $this->groupService->addCoreMember($travelPlan, $member);

        // 検証
        $this->assertEquals($coreGroup->id, $result->id);
        $this->assertEquals($coreGroup->id, $member->fresh()->group_id);
    }

    /**
     * @test
     */
    public function addCoreMember_コアグループが存在しない場合は新規作成する()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create([
            'title' => 'テスト旅行',
        ]);
        $otherGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => TravelPlan::factory()->create()->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $otherGroup->id,
        ]);

        // 実行前の確認
        $this->assertEquals(0, $travelPlan->groups()->core()->count());

        // 実行
        $result = $this->groupService->addCoreMember($travelPlan, $member);

        // 検証
        $this->assertEquals(1, $travelPlan->groups()->core()->count());
        $this->assertEquals('テスト旅行のメンバー', $result->name);
        $this->assertEquals(GroupType::CORE, $result->type);
        $this->assertEquals($travelPlan->id, $result->travel_plan_id);
        $this->assertEquals($result->id, $member->fresh()->group_id);
    }

    /**
     * @test
     */
    public function removeCoreMember_メンバーをコアグループから削除できる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);

        // 実行
        $result = $this->groupService->removeCoreMember($travelPlan, $member1);

        // 検証
        $this->assertEquals($coreGroup->id, $result->id);
        $this->assertFalse($member1->fresh()->is_active);
    }

    /**
     * @test
     */
    public function removeCoreMember_メンバーがコアグループに所属していない場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $otherGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => TravelPlan::factory()->create()->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $otherGroup->id,
        ]);

        // 期待
        $this->expectException(ModelNotFoundException::class);

        // 実行
        $this->groupService->removeCoreMember($travelPlan, $member);
    }

    /**
     * @test
     */
    public function removeCoreMember_コアグループの最後のメンバーは削除できない()
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

        // 期待
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('コアグループの最後のメンバーは削除できません。');

        // 実行
        $this->groupService->removeCoreMember($travelPlan, $member);
    }

    /**
     * @test
     */
    public function removeCoreMember_メンバーを削除すると関連する班グループからも削除される()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach([$member1->id, $member2->id]);

        // 実行
        $result = $this->groupService->removeCoreMember($travelPlan, $member1);

        // 検証
        $this->assertEquals($coreGroup->id, $result->id);
        $this->assertFalse($member1->fresh()->is_active);
        $this->assertFalse($branchGroup->members()->where('members.id', $member1->id)->exists());
    }

    /**
     * @test
     */
    public function createBranchMember_班グループを作成してメンバーを追加できる()
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
        $branchGroupName = 'テスト班';

        // 実行
        $result = $this->groupService->createBranchMember($travelPlan, $branchGroupName, $member);

        // 検証
        $this->assertEquals($branchGroupName, $result->name);
        $this->assertEquals(GroupType::BRANCH, $result->type);
        $this->assertEquals($travelPlan->id, $result->travel_plan_id);
        $this->assertEquals($coreGroup->id, $result->parent_group_id);
        $this->assertTrue($result->members()->where('members.id', $member->id)->exists());
    }

    /**
     * @test
     */
    public function createBranchMember_メンバーがコアグループに所属していない場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $otherGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => TravelPlan::factory()->create()->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $otherGroup->id,
        ]);
        $branchGroupName = 'テスト班';

        // 期待
        $this->expectException(ModelNotFoundException::class);

        // 実行
        $this->groupService->createBranchMember($travelPlan, $branchGroupName, $member);
    }

    /**
     * @test
     */
    public function addBranchMember_メンバーを班グループに追加できる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach($member1->id);

        // 実行
        $result = $this->groupService->addBranchMember($branchGroup, $member2);

        // 検証
        $this->assertEquals($branchGroup->id, $result->id);
        $this->assertTrue($result->members()->where('members.id', $member2->id)->exists());
    }

    /**
     * @test
     */
    public function addBranchMember_グループが班グループでない場合は例外を投げる()
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

        // 期待
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('指定されたグループは班グループではありません。');

        // 実行
        $this->groupService->addBranchMember($coreGroup, $member);
    }

    /**
     * @test
     */
    public function addBranchMember_メンバーがコアグループに所属していない場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $otherGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => TravelPlan::factory()->create()->id,
        ]);
        $member = Member::factory()->create([
            'group_id' => $otherGroup->id,
        ]);
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);

        // 期待
        $this->expectException(ModelNotFoundException::class);

        // 実行
        $this->groupService->addBranchMember($branchGroup, $member);
    }

    /**
     * @test
     */
    public function addBranchMember_メンバーが既に班グループに所属している場合は何もしない()
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
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach($member->id);

        // 実行
        $result = $this->groupService->addBranchMember($branchGroup, $member);

        // 検証
        $this->assertEquals($branchGroup->id, $result->id);
        $this->assertTrue($result->members()->where('members.id', $member->id)->exists());
    }

    /**
     * @test
     */
    public function removeBranchMember_メンバーを班グループから削除できる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach([$member1->id, $member2->id]);

        // 実行
        $result = $this->groupService->removeBranchMember($branchGroup, $member1);

        // 検証
        $this->assertEquals($branchGroup->id, $result->id);
        $this->assertFalse($result->members()->where('members.id', $member1->id)->exists());
        $this->assertTrue($result->members()->where('members.id', $member2->id)->exists());
    }

    /**
     * @test
     */
    public function removeBranchMember_グループが班グループでない場合は例外を投げる()
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

        // 期待
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('指定されたグループは班グループではありません。');

        // 実行
        $this->groupService->removeBranchMember($coreGroup, $member);
    }

    /**
     * @test
     */
    public function removeBranchMember_メンバーが班グループに所属していない場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach($member1->id);

        // 期待
        $this->expectException(ModelNotFoundException::class);

        // 実行
        $this->groupService->removeBranchMember($branchGroup, $member2);
    }

    /**
     * @test
     */
    public function removeBranchMember_精算データがある場合は例外を投げる()
    {
        // 準備
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach([$member1->id, $member2->id]);

        // 精算データを作成
        ExpenseSettlement::create([
            'travel_plan_id' => $travelPlan->id,
            'payer_member_id' => $member1->id,
            'receiver_member_id' => $member2->id,
            'amount' => 1000,
            'currency' => 'JPY',
        ]);

        // 期待
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('メンバーに精算データがあるため削除できません。');

        // 実行
        $this->groupService->removeBranchMember($branchGroup, $member1);
    }

    /**
     * @test
     */
    public function removeBranchMember_最後のメンバーを削除すると班グループも削除される()
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
        $branchGroup = Group::factory()->create([
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
            'parent_group_id' => $coreGroup->id,
        ]);
        $branchGroup->members()->attach($member->id);

        // 実行
        $result = $this->groupService->removeBranchMember($branchGroup, $member);

        // 検証
        $this->assertEquals($branchGroup->id, $result->id);
        $this->assertFalse($branchGroup->members()->exists());
        $this->assertNull(Group::find($branchGroup->id));
    }
} 