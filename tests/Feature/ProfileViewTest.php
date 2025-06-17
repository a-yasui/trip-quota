<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\OAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function profile_view_displays_correctly()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('アカウント設定');
        $response->assertSee('基本情報');
        $response->assertSee('アカウント一覧');
        $response->assertSee('OAuth連携');
        $response->assertSee('パスワード変更');
    }

    /** @test */
    public function profile_view_shows_basic_information()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee($this->user->email);
        $response->assertSee($this->user->created_at->format('Y年m月d日'));
    }

    /** @test */
    public function profile_view_shows_accounts_when_present()
    {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'account_name' => 'test_user',
            'display_name' => 'Test User'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('Test User');
        $response->assertSee('@test_user');
    }

    /** @test */
    public function profile_view_shows_empty_state_when_no_accounts()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('アカウントが設定されていません。');
    }

    /** @test */
    public function profile_view_shows_oauth_providers_when_present()
    {
        OAuthProvider::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'github',
            'provider_id' => '987654321'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('Github');
        $response->assertSee('連携済み');
    }

    /** @test */
    public function profile_view_shows_empty_state_when_no_oauth()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('OAuth連携がありません。');
    }

    /** @test */
    public function profile_view_shows_password_change_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('現在のパスワード');
        $response->assertSee('新しいパスワード');
        $response->assertSee('新しいパスワード（確認）');
        $response->assertSee('パスワードを変更');
    }

    /** @test */
    public function profile_view_shows_navigation_link()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('ダッシュボードに戻る');
    }

    /** @test */
    public function profile_view_shows_account_thumbnails()
    {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'account_name' => 'user_with_thumb',
            'display_name' => 'User With Thumbnail',
            'thumbnail_url' => 'https://example.com/thumb.jpg'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('https://example.com/thumb.jpg');
        $response->assertSee('User With Thumbnail');
    }

    /** @test */
    public function profile_view_shows_account_initials_when_no_thumbnail()
    {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'account_name' => 'example_user',
            'display_name' => 'Example User'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertSee('e'); // First character of account_name
    }
}