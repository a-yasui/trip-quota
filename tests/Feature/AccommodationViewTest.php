<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Member;
use App\Models\TravelPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private TravelPlan $travelPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->travelPlan = TravelPlan::factory()->create([
            'owner_user_id' => $this->user->id,
            'plan_name' => 'テスト旅行プラン',
            'departure_date' => '2024-07-01',
            'return_date' => '2024-07-05',
        ]);

        Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'user_id' => $this->user->id,
            'is_confirmed' => true,
        ]);
    }

    public function test_accommodation_index_view_renders_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('accommodations.index');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('accommodations');

        // 基本的なページ要素の確認
        $response->assertSee('宿泊施設管理');
        $response->assertSee('テスト旅行プラン');
        $response->assertSee('宿泊施設を追加');
        $response->assertSee('旅行プラン詳細に戻る');
    }

    public function test_accommodation_create_view_renders_correctly()
    {
        $member = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'テストメンバー',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.create', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('accommodations.create');
        $response->assertViewHas('travelPlan');
        $response->assertViewHas('members');

        // フォーム要素の確認
        $response->assertSee('宿泊施設を追加');
        $response->assertSee('宿泊施設名');
        $response->assertSee('住所');
        $response->assertSee('チェックイン日');
        $response->assertSee('チェックアウト日');
        $response->assertSee('1泊あたりの料金');
        $response->assertSee('通貨');
        $response->assertSee('予約番号・確認番号');
        $response->assertSee('宿泊メンバー');
        $response->assertSee('テストメンバー');
        $response->assertSee('メモ・備考');

        // 旅行期間の制約確認
        $response->assertSee('min="2024-07-01"', false);
        $response->assertSee('max="2024-07-05"', false);

        // 通貨オプションの確認
        $response->assertSee('JPY (円)');
        $response->assertSee('USD (ドル)');
        $response->assertSee('EUR (ユーロ)');

        // 注意事項の確認
        $response->assertSee('チェックアウト日はチェックイン日より後の日付を設定してください');
        $response->assertSee('宿泊日程は旅行期間内である必要があります');
    }

    public function test_accommodation_index_displays_accommodation_details()
    {
        $member1 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー1',
        ]);

        $member2 = Member::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'メンバー2',
        ]);

        $accommodation = Accommodation::factory()->create([
            'travel_plan_id' => $this->travelPlan->id,
            'name' => 'グランドホテル東京',
            'address' => '東京都千代田区大手町1-1-1',
            'check_in_date' => '2024-07-01',
            'check_out_date' => '2024-07-03',
            'price_per_night' => 25000,
            'currency' => 'JPY',
            'confirmation_number' => 'ABC123456',
        ]);

        $accommodation->members()->attach([$member1->id, $member2->id]);

        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);

        // 宿泊施設の詳細情報
        $response->assertSee('グランドホテル東京');
        $response->assertSee('東京都千代田区大手町1-1-1');
        $response->assertSee('2024/07/01');
        $response->assertSee('2024/07/03');
        $response->assertSee('25,000 JPY/泊');
        $response->assertSee('2人'); // メンバー数
        $response->assertSee('ABC123456');

        // リンクの確認
        $response->assertSee('詳細');
        $response->assertSee('編集');
    }

    public function test_accommodation_index_empty_state()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('宿泊施設がありません');
        $response->assertSee('最初の宿泊施設を追加してみましょう。');
    }

    public function test_accommodation_create_form_defaults()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.create', $this->travelPlan->uuid));

        $response->assertStatus(200);

        // デフォルト値の確認
        $response->assertSee('value="2024-07-01"', false); // デフォルトのチェックイン日
        $response->assertSee('selected'); // JPYがデフォルト選択
    }

    public function test_accommodation_create_form_validation_errors_display()
    {
        $response = $this->actingAs($this->user)
            ->from(route('travel-plans.accommodations.create', $this->travelPlan->uuid))
            ->post(route('travel-plans.accommodations.store', $this->travelPlan->uuid), [
                'name' => '', // 必須項目を空に
                'check_in_date' => '',
                'check_out_date' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'check_in_date', 'check_out_date']);
        $response->assertRedirect(route('travel-plans.accommodations.create', $this->travelPlan->uuid));

        // エラーページのリダイレクト先を確認
        $followResponse = $this->followRedirects($response);
        $followResponse->assertSee('宿泊施設を追加');
    }

    public function test_accommodation_form_javascript_functionality()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.create', $this->travelPlan->uuid));

        $response->assertStatus(200);

        // JavaScriptコードの存在確認
        $response->assertSee('checkInDate.addEventListener');
        $response->assertSee('checkOutDate.min = nextDay.toISOString()');
        $response->assertSee('DOMContentLoaded');
    }

    public function test_accommodation_view_includes_master_layout()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);
        $response->assertSee('TripQuota'); // タイトルに含まれるアプリ名
    }

    public function test_accommodation_navigation_links()
    {
        $response = $this->actingAs($this->user)
            ->get(route('travel-plans.accommodations.index', $this->travelPlan->uuid));

        $response->assertStatus(200);

        // ナビゲーションリンクの確認
        $backUrl = route('travel-plans.show', $this->travelPlan->uuid);
        $createUrl = route('travel-plans.accommodations.create', $this->travelPlan->uuid);

        $response->assertSee($backUrl);
        $response->assertSee($createUrl);
    }
}
