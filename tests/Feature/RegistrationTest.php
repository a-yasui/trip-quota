<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('新規登録');
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_name' => 'testuser',
            'display_name' => 'Test User',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        // ユーザーが作成されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // アカウントが作成されているか確認
        $this->assertDatabaseHas('accounts', [
            'account_name' => 'testuser',
            'display_name' => 'Test User',
        ]);

        // ユーザー設定が作成されているか確認
        $user = User::where('email', 'test@example.com')->first();
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'language' => 'ja',
            'timezone' => 'Asia/Tokyo',
        ]);
    }

    public function test_registration_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_name' => 'testuser',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_duplicate_account_name()
    {
        $user = User::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'existinguser',
        ]);

        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_name' => 'existinguser',
        ]);

        $response->assertSessionHasErrors(['account_name']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_invalid_account_name()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_name' => '123invalid', // 数字で始まる
        ]);

        $response->assertSessionHasErrors(['account_name']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_weak_password()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'account_name' => 'testuser',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_password_mismatch()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
            'account_name' => 'testuser',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_registration_uses_account_name_as_display_name_when_not_provided()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'account_name' => 'testuser',
        ]);

        $this->assertAuthenticated();

        $this->assertDatabaseHas('accounts', [
            'account_name' => 'testuser',
            'display_name' => 'testuser',
        ]);
    }

    public function test_authenticated_users_cannot_access_registration_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/dashboard');
    }
}
