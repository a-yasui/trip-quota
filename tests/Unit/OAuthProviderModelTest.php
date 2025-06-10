<?php

namespace Tests\Unit;

use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OAuthProviderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_provider_can_be_created_with_valid_data()
    {
        $user = User::factory()->create();

        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $this->assertDatabaseHas('oauth_providers', [
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $this->assertInstanceOf(OAuthProvider::class, $provider);
    }

    public function test_oauth_provider_belongs_to_user()
    {
        $user = User::factory()->create();
        $provider = OAuthProvider::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $provider->user);
        $this->assertEquals($user->id, $provider->user->id);
    }

    public function test_find_by_provider()
    {
        $user = User::factory()->create();

        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $found = OAuthProvider::findByProvider('google', '123456789');
        $this->assertNotNull($found);
        $this->assertEquals($provider->id, $found->id);

        $notFound = OAuthProvider::findByProvider('github', '123456789');
        $this->assertNull($notFound);
    }

    public function test_is_token_valid_with_future_expiry()
    {
        $user = User::factory()->create();

        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'expires_at' => now()->addHour(),
        ]);

        $this->assertTrue($provider->isTokenValid());
    }

    public function test_is_token_valid_with_past_expiry()
    {
        $user = User::factory()->create();

        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'expires_at' => now()->subHour(),
        ]);

        $this->assertFalse($provider->isTokenValid());
    }

    public function test_is_token_valid_with_null_expiry()
    {
        $user = User::factory()->create();

        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'expires_at' => null,
        ]);

        $this->assertTrue($provider->isTokenValid());
    }

    public function test_unique_provider_constraint()
    {
        $user = User::factory()->create();

        OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // 同じプロバイダー + プロバイダーIDの組み合わせでは作成できない
        OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);
    }
}
