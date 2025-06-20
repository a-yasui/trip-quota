<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberControllerTest extends TestCase
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
            'creator_user_id' => $this->user->id,
            'owner_user_id' => $this->user->id,
        ]);
        // Create core group (required for member operations)
        Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'CORE',
            'name' => 'コアグループ',
        ]);

        $this->member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_index_displays_members_for_authenticated_member()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('メンバー管理');
        $response->assertSee($this->travelPlan->plan_name);
    }

    public function test_create_displays_invitation_form()
    {
        // デバッグ: メンバーが正しく設定されているか確認
        $this->assertDatabaseHas('members', [
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('メンバー追加');
        $response->assertSee('追加方法');
        $response->assertSee('表示名のみで追加');
        $response->assertSee('招待付きで追加');
    }

    public function test_store_creates_member_by_name_only()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'name_only',
                'name' => 'Test User',
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');

        // メンバーがDBに作成されていることを確認
        $this->assertDatabaseHas('members', [
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test User',
            'user_id' => null,
            'email' => null,
        ]);
    }

    public function test_store_creates_member_with_name_only()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'name_only',
                'name' => 'Name Only Member',
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success', 'メンバー「Name Only Member」を追加しました。');

        // メンバーが追加され、即座に確認済みになることを確認
        $this->assertDatabaseHas('members', [
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Name Only Member',
            'user_id' => null,
            'is_confirmed' => true, // 表示名のみメンバーは即座に確認済み
        ]);
    }

    public function test_store_creates_invitation_by_email()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'with_invitation',
                'invitation_type' => 'email',
                'email' => 'test@example.com',
                'name' => 'Test User',
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');
    }

    public function test_store_creates_invitation_by_account_name()
    {
        $targetUser = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $targetUser->id,
            'account_name' => 'testuser123',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'with_invitation',
                'invitation_type' => 'account',
                'account_name' => 'testuser123',
                'name' => 'Test User',
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), []);

        $response->assertSessionHasErrors(['member_type', 'name']);
    }

    public function test_store_validates_email_required_when_invitation_type_is_email()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'with_invitation',
                'name' => 'Test User',
                'invitation_type' => 'email',
                'email' => '', // 空のメール
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_validates_account_name_required_when_invitation_type_is_account()
    {
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'with_invitation',
                'name' => 'Test User',
                'invitation_type' => 'account',
                'account_name' => '', // 空のアカウント名
            ]);

        $response->assertSessionHasErrors(['account_name']);
    }

    public function test_store_name_only_does_not_require_invitation_fields()
    {
        // 表示名のみの場合、invitation_typeやemailが送信されても問題ないことを確認
        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.store', $this->travelPlan->uuid), [
                'member_type' => 'name_only',
                'name' => 'Name Only Test',
                'invitation_type' => 'email', // これがあってもエラーにならない
                'email' => '', // 空でも問題ない
            ]);

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_show_displays_member_details()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Other Member',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.show', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertStatus(200);
        $response->assertSee($otherMember->name);
    }

    public function test_edit_displays_form_with_existing_data()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Editable Member',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.edit', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertStatus(200);
        $response->assertSee('メンバー編集');
        $response->assertSee('value="Editable Member"', false);
    }

    public function test_update_modifies_member_with_valid_data()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Original Name',
            'is_confirmed' => true,
        ]);

        // メールアドレスがある場合は、更新時にもメールアドレスを含める必要がある
        $updateData = ['name' => 'Updated Name'];
        if ($otherMember->email) {
            $updateData['email'] = $otherMember->email;
        }

        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $otherMember->id]), $updateData);

        $response->assertRedirect(route('travel-plans.members.show', [$this->travelPlan->uuid, $otherMember->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'id' => $otherMember->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_member()
    {
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.members.destroy', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertRedirect(route('travel-plans.members.index', $this->travelPlan->uuid));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('members', ['id' => $otherMember->id]);
    }

    public function test_unauthorized_user_cannot_access_members()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.members.index', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_non_member_cannot_create_invitation()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('travel-plans.members.create', $this->travelPlan->uuid));

        $response->assertStatus(403);
    }

    public function test_user_cannot_edit_others_in_different_travel_plan()
    {
        $otherTravelPlan = TravelPlan::factory()->create();
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $otherTravelPlan->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.members.edit', [$otherTravelPlan->uuid, $otherMember->id]));

        $response->assertStatus(403);
    }

    public function test_travel_plan_creator_can_confirm_member()
    {
        // 旅行プラン作成者として未確認メンバーを作成
        $unconfirmedMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Unconfirmed Member',
            'user_id' => null,
            'is_confirmed' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('travel-plans.members.confirm', [$this->travelPlan->uuid, $unconfirmedMember->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'メンバー「Unconfirmed Member」を確認済みにしました。');

        // メンバーが確認済みになったことを確認
        $this->assertDatabaseHas('members', [
            'id' => $unconfirmedMember->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_non_creator_cannot_confirm_member()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $otherUser->id,
            'is_confirmed' => true,
        ]);

        // 未確認メンバーを作成
        $unconfirmedMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Unconfirmed Member',
            'user_id' => null,
            'is_confirmed' => false,
        ]);

        // 作成者でないユーザーが確認しようとする
        $response = $this->actingAs($otherUser)
            ->post(route('travel-plans.members.confirm', [$this->travelPlan->uuid, $unconfirmedMember->id]));

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // メンバーが未確認のままであることを確認
        $this->assertDatabaseHas('members', [
            'id' => $unconfirmedMember->id,
            'is_confirmed' => false,
        ]);
    }

    public function test_travel_plan_creator_can_delete_any_member()
    {
        // 別のユーザーのメンバーを作成
        $otherUser = User::factory()->create();
        $otherMember = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $otherUser->id,
            'is_confirmed' => true,
        ]);

        // 旅行プラン作成者が削除
        $response = $this->actingAs($this->user)
            ->delete(route('travel-plans.members.destroy', [$this->travelPlan->uuid, $otherMember->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // メンバーが削除されたことを確認
        $this->assertDatabaseMissing('members', [
            'id' => $otherMember->id,
        ]);
    }

    public function test_update_member_without_email_does_not_require_email()
    {
        // メールアドレスなしのメンバーを作成
        $memberWithoutEmail = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => null,
            'email' => null,
            'name' => 'Member Without Email',
        ]);

        // メールアドレスなしで更新（エラーにならないことを確認）
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $memberWithoutEmail->id]), [
                'name' => 'Updated Name',
                // emailフィールドは送信しない
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('members', [
            'id' => $memberWithoutEmail->id,
            'name' => 'Updated Name',
            'email' => null,
        ]);
    }

    public function test_update_member_with_email_requires_email()
    {
        // メールアドレスありのメンバーを作成
        $memberWithEmail = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => null,
            'email' => 'original@example.com',
            'name' => 'Member With Email',
        ]);

        // メールアドレスを空にして更新（エラーになることを確認）
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $memberWithEmail->id]), [
                'name' => 'Updated Name',
                'email' => '', // 空のメール
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_member_can_add_email_optionally()
    {
        // メールアドレスなしのメンバーを作成
        $memberWithoutEmail = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => null,
            'email' => null,
            'name' => 'Member Without Email',
        ]);

        // メールアドレスを追加して更新
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $memberWithoutEmail->id]), [
                'name' => 'Updated Name',
                'email' => 'new@example.com',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'id' => $memberWithoutEmail->id,
            'name' => 'Updated Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_update_member_can_join_groups()
    {
        // 追加のグループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '班グループ1',
        ]);

        // メンバーを作成（最初はどのグループにも所属していない）
        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Member',
            'is_confirmed' => true,
        ]);

        // コアグループを取得
        $coreGroup = $this->travelPlan->groups()->where('type', 'CORE')->first();

        // 更新データを準備（メールアドレスがある場合は含める）
        $updateData = [
            'name' => $member->name,
            'groups' => [$coreGroup->id, $branchGroup->id],
        ];
        if ($member->email) {
            $updateData['email'] = $member->email;
        }

        // メンバーをグループに追加
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $member->id]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // メンバーがグループに所属したことを確認
        $member->refresh();
        $this->assertTrue($member->groups->contains($coreGroup));
        $this->assertTrue($member->groups->contains($branchGroup));
        $this->assertEquals(2, $member->groups->count());
    }

    public function test_update_member_can_leave_groups()
    {
        // 追加のグループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '班グループ1',
        ]);

        // メンバーを作成
        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Member',
            'is_confirmed' => true,
        ]);

        // コアグループを取得
        $coreGroup = $this->travelPlan->groups()->where('type', 'CORE')->first();

        // 最初に両方のグループに所属させる
        $member->groups()->sync([$coreGroup->id, $branchGroup->id]);
        $this->assertEquals(2, $member->groups->count());

        // 更新データを準備（メールアドレスがある場合は含める）
        $updateData = [
            'name' => $member->name,
            'groups' => [$coreGroup->id], // 班グループを除外
        ];
        if ($member->email) {
            $updateData['email'] = $member->email;
        }

        // 班グループから除外（コアグループのみに変更）
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $member->id]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // メンバーが班グループから除外されたことを確認
        $member->refresh();
        $this->assertTrue($member->groups->contains($coreGroup));
        $this->assertFalse($member->groups->contains($branchGroup));
        $this->assertEquals(1, $member->groups->count());
    }

    public function test_update_member_can_remove_from_all_groups()
    {
        // 追加のグループを作成
        $branchGroup = Group::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'type' => 'BRANCH',
            'name' => '班グループ1',
        ]);

        // メンバーを作成
        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Member',
            'is_confirmed' => true,
        ]);

        // コアグループを取得
        $coreGroup = $this->travelPlan->groups()->where('type', 'CORE')->first();

        // 最初に両方のグループに所属させる
        $member->groups()->sync([$coreGroup->id, $branchGroup->id]);
        $this->assertEquals(2, $member->groups->count());

        // 更新データを準備（メールアドレスがある場合は含める）
        $updateData = [
            'name' => $member->name,
            'groups' => [], // 空の配列
        ];
        if ($member->email) {
            $updateData['email'] = $member->email;
        }

        // すべてのグループから除外
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $member->id]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // メンバーがすべてのグループから除外されたことを確認
        $member->refresh();
        $this->assertEquals(0, $member->groups->count());
    }

    public function test_update_member_validates_groups_belong_to_travel_plan()
    {
        // 別の旅行プランのグループを作成
        $otherTravelPlan = TravelPlan::factory()->create([
            'creator_user_id' => $this->user->id,
            'owner_user_id' => $this->user->id,
        ]);
        $otherGroup = Group::factory()->create([
            'travel_plan_id' => $otherTravelPlan->id,
            'type' => 'CORE',
            'name' => '他のプランのグループ',
        ]);

        // メンバーを作成
        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'Test Member',
            'is_confirmed' => true,
        ]);

        // 更新データを準備（メールアドレスがある場合は含める）
        $updateData = [
            'name' => $member->name,
            'groups' => [$otherGroup->id], // 別の旅行プランのグループ
        ];
        if ($member->email) {
            $updateData['email'] = $member->email;
        }

        // 他の旅行プランのグループを指定して更新（無効なグループIDは無視される）
        $response = $this->actingAs($this->user)
            ->put(route('travel-plans.members.update', [$this->travelPlan->uuid, $member->id]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 無効なグループには所属しないことを確認
        $member->refresh();
        $this->assertEquals(0, $member->groups->count());
    }
}
