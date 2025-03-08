<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * ログイン画面にGoogleとFacebookのログインボタンが表示されるかテスト
     */
    public function test_login_page_displays_social_login_buttons(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Googleでログイン');
        $response->assertSee('Facebookでログイン');
        $response->assertSee(route('socialite.redirect', 'google'));
        $response->assertSee(route('socialite.redirect', 'facebook'));
    }

    /**
     * プロフィール画面にアクセスできるかテスト
     */
    public function test_profile_page_can_be_accessed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Profile');
    }

    /**
     * 無効なプロバイダーへのアクセスが404を返すかテスト
     */
    public function test_invalid_provider_returns_404(): void
    {
        $response = $this->get('/auth/invalid-provider');
        $response->assertStatus(404);

        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post('/auth/invalid-provider/connect');
        $response->assertStatus(404);
    }
}
