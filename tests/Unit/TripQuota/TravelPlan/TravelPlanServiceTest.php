<?php

namespace Tests\Unit\TripQuota\TravelPlan;

use App\Enums\GroupType;
use App\Models\ExpenseSettlement;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use TripQuota\TravelPlan\GroupCreateResult;
use TripQuota\TravelPlan\TravelPlanService;

class TravelPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    private TravelPlanService $travelPlanService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelPlanService = new TravelPlanService();
    }

    /**
     * 旅行計画とコアグループを作成できるかテスト
     */
    public function test_create_travel_plan(): void
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create();
        
        // テスト用の旅行計画名
        $planName = '韓国ソウル旅行';

        // サービスを使用して旅行計画とコアグループを作成
        $result = $this->travelPlanService->create($planName);

        // 戻り値が GroupCreateResult クラスのインスタンスであることを確認
        $this->assertInstanceOf(GroupCreateResult::class, $result);

        // 旅行計画とコアグループが作成されていることを確認
        $this->assertInstanceOf(TravelPlan::class, $result->plan);
        $this->assertInstanceOf(Group::class, $result->core_group);

        // 旅行計画名が正しく設定されていることを確認
        $this->assertEquals($planName, $result->plan->title);
        
        // 必須フィールドを設定
        $result->plan->creator_id = $user->id;
        $result->plan->deletion_permission_holder_id = $user->id;
        $result->plan->departure_date = now()->addDays(30);
        $result->plan->timezone = 'Asia/Tokyo';
        $result->plan->save();

        // コアグループが旅行計画に紐づいていることを確認
        $this->assertEquals($result->plan->id, $result->core_group->travel_plan_id);

        // コアグループのタイプがCOREであることを確認
        $this->assertEquals(GroupType::CORE, $result->core_group->type);

        // コアグループの名前が旅行計画名 + 'のメンバー'となっていることを確認
        $this->assertEquals($planName . 'のメンバー', $result->core_group->name);
    }

    /**
     * 旅行計画に班グループを追加できるかテスト
     */
    public function test_add_branch_group(): void
    {
        // テスト用の旅行計画とコアグループを作成
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // テスト用の班グループ名
        $branchName = '班グループA';

        // サービスを使用して班グループを作成
        $branchGroup = $this->travelPlanService->addBranchGroup($travelPlan, $branchName);

        // 班グループが作成されていることを確認
        $this->assertInstanceOf(Group::class, $branchGroup);

        // 班グループ名が正しく設定されていることを確認
        $this->assertEquals($branchName, $branchGroup->name);

        // 班グループのタイプがBRANCHであることを確認
        $this->assertEquals(GroupType::BRANCH, $branchGroup->type);

        // 班グループが旅行計画に紐づいていることを確認
        $this->assertEquals($travelPlan->id, $branchGroup->travel_plan_id);

        // 親グループがコアグループであることを確認
        $this->assertEquals($coreGroup->id, $branchGroup->parent_group_id);
    }

    /**
     * 班グループを削除できるかテスト
     */
    public function test_remove_branch_group(): void
    {
        // テスト用の旅行計画とコアグループを作成
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // テスト用の班グループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::BRANCH,
            'parent_group_id' => $coreGroup->id,
        ]);

        // サービスを使用して班グループを削除
        $this->travelPlanService->removeBranchGroup($branchGroup);

        // 班グループが削除されていることを確認（SoftDeletesを考慮）
        $this->assertSoftDeleted('groups', [
            'id' => $branchGroup->id,
        ]);
    }

    /**
     * コアグループは削除できないことをテスト
     */
    public function test_cannot_remove_core_group(): void
    {
        // テスト用の旅行計画とコアグループを作成
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // コアグループを削除しようとすると例外が発生することを確認
        $this->expectException(\InvalidArgumentException::class);
        $this->travelPlanService->removeBranchGroup($coreGroup);
    }

    /**
     * 精算データがある班グループは削除できないことをテスト
     */
    public function test_cannot_remove_branch_group_with_expense_settlements(): void
    {
        // テスト用の旅行計画とコアグループを作成
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // テスト用の班グループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::BRANCH,
            'parent_group_id' => $coreGroup->id,
        ]);

        // テスト用のメンバーを作成
        $member1 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);
        $member2 = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);

        // メンバーを班グループに追加
        $branchGroup->members()->attach([$member1->id, $member2->id]);

        // 精算データを作成
        ExpenseSettlement::factory()->create([
            'payer_member_id' => $member1->id,
            'receiver_member_id' => $member2->id,
            'travel_plan_id' => $travelPlan->id,
            'amount' => 1000, // 金額を設定
            'currency' => 'JPY', // 通貨を設定
        ]);

        // 精算データがある班グループを削除しようとすると例外が発生することを確認
        $this->expectException(\Exception::class);
        $this->travelPlanService->removeBranchGroup($branchGroup);
    }

    /**
     * 旅行計画を削除できるかテスト
     */
    public function test_remove_travel_plan(): void
    {
        // テスト用の旅行計画とコアグループを作成
        $travelPlan = TravelPlan::factory()->create();
        $coreGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::CORE,
        ]);

        // テスト用の班グループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $travelPlan->id,
            'type' => GroupType::BRANCH,
            'parent_group_id' => $coreGroup->id,
        ]);

        // テスト用のメンバーを作成
        $member = Member::factory()->create([
            'group_id' => $coreGroup->id,
        ]);

        // メンバーを班グループに追加
        $branchGroup->members()->attach($member->id);

        // サービスを使用して旅行計画を削除
        $this->travelPlanService->removeTravelPlan($travelPlan);

        // 旅行計画が削除されていることを確認
        $this->assertSoftDeleted('travel_plans', [
            'id' => $travelPlan->id,
        ]);

        // コアグループが削除されていることを確認
        $this->assertSoftDeleted('groups', [
            'id' => $coreGroup->id,
        ]);

        // 班グループが削除されていることを確認
        $this->assertSoftDeleted('groups', [
            'id' => $branchGroup->id,
        ]);

        // メンバーが非アクティブになっていることを確認
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'is_active' => false,
        ]);
    }
} 