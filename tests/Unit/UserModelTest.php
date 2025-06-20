<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\OAuthProvider;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_valid_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_has_password_when_password_is_set()
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->assertTrue($user->hasPassword());
    }

    public function test_user_does_not_have_password_when_password_is_null()
    {
        $user = User::factory()->create([
            'password' => null,
        ]);

        $this->assertFalse($user->hasPassword());
    }

    public function test_user_can_have_multiple_accounts()
    {
        $user = User::factory()->create();

        Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'account1',
        ]);

        Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'account2',
        ]);

        $this->assertCount(2, $user->accounts);
    }

    public function test_user_can_have_oauth_providers()
    {
        $user = User::factory()->create();

        OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
        ]);

        $this->assertTrue($user->hasOAuthProvider('google'));
        $this->assertFalse($user->hasOAuthProvider('github'));
    }

    public function test_user_has_user_settings_relationship()
    {
        $user = User::factory()->create();

        UserSetting::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(UserSetting::class, $user->userSettings);
    }

    public function test_password_is_hashed_automatically()
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }
}
