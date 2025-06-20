<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\OAuthProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function show_displays_profile_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
        $response->assertSee('アカウント設定');
        $response->assertSee($this->user->email);
    }

    #[Test]
    public function show_displays_user_accounts()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'account_name' => 'test_account',
            'display_name' => 'Test Account',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Test Account');
        $response->assertSee('@test_account');
    }

    #[Test]
    public function show_displays_oauth_providers()
    {
        OAuthProvider::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Google');
        $response->assertSee('連携済み');
    }

    #[Test]
    public function update_password_successfully_changes_password()
    {
        $currentPassword = 'current_password';
        $newPassword = 'new_password123!';

        $this->user->update([
            'password' => Hash::make($currentPassword),
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('profile.password.update'), [
                'current_password' => $currentPassword,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success', 'パスワードを変更しました。');

        // パスワードが変更されたことを確認
        $this->assertTrue(Hash::check($newPassword, $this->user->fresh()->password));
    }

    #[Test]
    public function update_password_fails_with_wrong_current_password()
    {
        $currentPassword = 'current_password';
        $wrongPassword = 'wrong_password';
        $newPassword = 'new_password123!';

        $this->user->update([
            'password' => Hash::make($currentPassword),
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('profile.password.update'), [
                'current_password' => $wrongPassword,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

        $response->assertSessionHasErrors(['current_password']);

        // パスワードが変更されていないことを確認
        $this->assertTrue(Hash::check($currentPassword, $this->user->fresh()->password));
    }

    #[Test]
    public function update_password_fails_with_password_confirmation_mismatch()
    {
        $currentPassword = 'current_password';
        $newPassword = 'new_password123!';

        $this->user->update([
            'password' => Hash::make($currentPassword),
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('profile.password.update'), [
                'current_password' => $currentPassword,
                'password' => $newPassword,
                'password_confirmation' => 'different_password',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function update_password_fails_with_weak_password()
    {
        $currentPassword = 'current_password';
        $weakPassword = '123';

        $this->user->update([
            'password' => Hash::make($currentPassword),
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('profile.password.update'), [
                'current_password' => $currentPassword,
                'password' => $weakPassword,
                'password_confirmation' => $weakPassword,
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function update_password_requires_authentication()
    {
        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'some_password',
            'password' => 'new_password123!',
            'password_confirmation' => 'new_password123!',
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_requires_authentication()
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }
}
