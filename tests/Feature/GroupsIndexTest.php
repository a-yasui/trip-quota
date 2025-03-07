<?php

namespace Tests\Feature;

use App\Enums\GroupType;
use App\Models\Group;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupsIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザーがどのグループにも属していない場合のテスト
     */
    public function test_groups_index_shows_no_groups_message_when_user_has_no_groups(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // グループページにアクセス
        $response = $this->actingAs($user)->get('/groups');

        // ステータスコードとビューの確認
        $response->assertStatus(200);
        $response->assertViewIs('groups.index');

        // 「旅行はありません」メッセージの確認
        $response->assertSee('今後の旅行予定はありません');
        $response->assertSee('現在進行中の旅行はありません');
        $response->assertSee('過去の旅行記録はありません');
    }

    /**
     * ユーザーが今後の旅行のグループに属している場合のテスト
     */
    public function test_groups_index_shows_future_groups_with_links(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 未来の旅行計画を作成
        $travelPlan = TravelPlan::factory()->create([
            'departure_date' => now()->addDays(10),
            'return_date' => now()->addDays(15),
        ]);

        // コアグループを作成
        $group = Group::factory()->create([
            'name' => '未来旅行グループ',
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);

        // ユーザーをグループのメンバーとして追加
        Member::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        // グループページにアクセス
        $response = $this->actingAs($user)->get('/groups');

        // ステータスコードとビューの確認
        $response->assertStatus(200);
        $response->assertViewIs('groups.index');

        // グループ名が表示されていることを確認
        $response->assertSee('未来旅行グループ');
        
        // リンクが正しく設定されていることを確認
        $response->assertSee(route('travel-plans.show', $travelPlan->id));
    }

    /**
     * ユーザーが現在進行中の旅行のグループに属している場合のテスト
     */
    public function test_groups_index_shows_current_groups_with_links(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 現在進行中の旅行計画を作成
        $travelPlan = TravelPlan::factory()->create([
            'departure_date' => now()->subDays(2),
            'return_date' => now()->addDays(3),
        ]);

        // ブランチグループを作成
        $group = Group::factory()->create([
            'name' => '現在進行中グループ',
            'type' => GroupType::BRANCH,
            'travel_plan_id' => $travelPlan->id,
        ]);

        // ユーザーをグループのメンバーとして追加
        Member::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        // グループページにアクセス
        $response = $this->actingAs($user)->get('/groups');

        // ステータスコードとビューの確認
        $response->assertStatus(200);
        $response->assertViewIs('groups.index');

        // グループ名が表示されていることを確認
        $response->assertSee('現在進行中グループ');
        
        // リンクが正しく設定されていることを確認（ブランチグループの場合）
        $response->assertSee(route('branch-groups.show', $group->id));
    }

    /**
     * ユーザーが過去の旅行のグループに属している場合のテスト
     */
    public function test_groups_index_shows_past_groups_with_links(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 過去の旅行計画を作成
        $travelPlan = TravelPlan::factory()->create([
            'departure_date' => now()->subDays(10),
            'return_date' => now()->subDays(5),
        ]);

        // コアグループを作成
        $group = Group::factory()->create([
            'name' => '過去旅行グループ',
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);

        // ユーザーをグループのメンバーとして追加
        Member::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);

        // グループページにアクセス
        $response = $this->actingAs($user)->get('/groups');

        // ステータスコードとビューの確認
        $response->assertStatus(200);
        $response->assertViewIs('groups.index');

        // グループ名が表示されていることを確認
        $response->assertSee('過去旅行グループ');
        
        // リンクが正しく設定されていることを確認
        $response->assertSee(route('travel-plans.show', $travelPlan->id));
    }

    /**
     * 自身が属していないグループが表示されていないことを確認するテスト
     */
    public function test_groups_index_does_not_show_groups_user_is_not_member_of(): void
    {
        // ユーザーAを作成（テスト対象のユーザー）
        $userA = User::factory()->create();
        
        // ユーザーBを作成（グループに所属するユーザー）
        $userB = User::factory()->create();

        // 旅行計画を作成
        $travelPlan = TravelPlan::factory()->create([
            'departure_date' => now()->addDays(10),
            'return_date' => now()->addDays(15),
        ]);

        // グループを作成
        $group = Group::factory()->create([
            'name' => '他人の旅行グループ',
            'type' => GroupType::CORE,
            'travel_plan_id' => $travelPlan->id,
        ]);

        // ユーザーBをグループのメンバーとして追加
        Member::factory()->create([
            'user_id' => $userB->id,
            'group_id' => $group->id,
        ]);

        // ユーザーAとしてグループページにアクセス
        $response = $this->actingAs($userA)->get('/groups');

        // ステータスコードとビューの確認
        $response->assertStatus(200);
        $response->assertViewIs('groups.index');

        // ユーザーBのグループ名が表示されていないことを確認
        $response->assertDontSee('他人の旅行グループ');
        
        // 「旅行はありません」メッセージが表示されていることを確認
        $response->assertSee('今後の旅行予定はありません');
        $response->assertSee('現在進行中の旅行はありません');
        $response->assertSee('過去の旅行記録はありません');
    }
}
