<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\OAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // OAuth設定を有効にする
        Config::set('services.google', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function test_oauth_redirect_for_valid_provider()
    {
        $response = $this->get('/auth/google');

        // リダイレクト先にGoogleのOAuth URLが含まれることを確認
        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->getTargetUrl());
    }

    public function test_oauth_redirect_for_invalid_provider()
    {
        $response = $this->get('/auth/invalid-provider');

        $response->assertStatus(404);
    }

    public function test_oauth_callback_creates_new_user_with_oauth()
    {
        // Socialiteをモック
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Test User');
        $socialiteUser->shouldReceive('getNickname')->andReturn('testuser');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->token = 'mock-access-token';
        $socialiteUser->refreshToken = 'mock-refresh-token';
        $socialiteUser->expiresIn = 3600;

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        // 新しいユーザーが作成されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // OAuth連携が作成されているか確認
        $this->assertDatabaseHas('oauth_providers', [
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        // アカウントが作成されているか確認
        $this->assertDatabaseCount('accounts', 1);
    }

    public function test_oauth_callback_links_to_existing_user_by_email()
    {
        // 既存ユーザーを作成
        $existingUser = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Socialiteをモック
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Test User');
        $socialiteUser->shouldReceive('getNickname')->andReturn('testuser');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialiteUser->token = 'mock-access-token';
        $socialiteUser->refreshToken = 'mock-refresh-token';
        $socialiteUser->expiresIn = 3600;

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $this->assertEquals($existingUser->id, auth()->id());

        // 既存ユーザーにOAuth連携が追加されているか確認
        $this->assertDatabaseHas('oauth_providers', [
            'user_id' => $existingUser->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);
    }

    public function test_oauth_callback_logs_in_existing_oauth_user()
    {
        // 既存のOAuth連携ユーザーを作成
        $user = User::factory()->create();
        $oauthProvider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        // Socialiteをモック
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
        $socialiteUser->token = 'new-access-token';
        $socialiteUser->refreshToken = 'new-refresh-token';
        $socialiteUser->expiresIn = 3600;

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $this->assertEquals($user->id, auth()->id());

        // トークンが更新されているか確認
        $oauthProvider->refresh();
        $this->assertEquals('new-access-token', $oauthProvider->access_token);
    }

    public function test_oauth_callback_handles_socialite_exception()
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andThrow(new \Exception('OAuth error'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}