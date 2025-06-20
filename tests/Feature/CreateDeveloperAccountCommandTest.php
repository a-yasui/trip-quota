<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateDeveloperAccountCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_developer_account_with_options(): void
    {
        $email = 'developer@example.com';
        $password = 'password123';

        $this->artisan('developer:create-account', [
            '--email' => $email,
            '--password' => $password,
        ])
            ->expectsOutput('開発者アカウントを作成しました:')
            ->expectsOutput("  メールアドレス: {$email}")
            ->assertExitCode(0);

        // ユーザーが作成されているかチェック
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotNull($user->email_verified_at);

        // アカウントが作成されているかチェック
        $account = Account::where('user_id', $user->id)->first();
        $this->assertNotNull($account);
        $this->assertEquals('dev'.$user->id, $account->account_name);
        $this->assertEquals('Developer Account', $account->display_name);
    }

    public function test_create_developer_account_with_duplicate_email(): void
    {
        $email = 'developer@example.com';

        // 既存ユーザーを作成
        User::factory()->create(['email' => $email]);

        $this->artisan('developer:create-account', [
            '--email' => $email,
            '--password' => 'password123',
        ])
            ->expectsOutput("アカウント作成に失敗しました: メールアドレス「{$email}」は既に使用されています")
            ->assertExitCode(1);
    }

    public function test_create_developer_account_with_invalid_email(): void
    {
        $this->artisan('developer:create-account', [
            '--email' => 'invalid-email',
            '--password' => 'password123',
        ])
            ->expectsOutput('有効なメールアドレスを入力してください')
            ->assertExitCode(1);
    }

    public function test_create_developer_account_with_short_password(): void
    {
        $this->artisan('developer:create-account', [
            '--email' => 'developer@example.com',
            '--password' => '123',
        ])
            ->expectsOutput('パスワードは8文字以上で入力してください')
            ->assertExitCode(1);
    }

    public function test_create_developer_account_interactive_mode(): void
    {
        $email = 'interactive@example.com';
        $password = 'password123';

        $this->artisan('developer:create-account')
            ->expectsQuestion('メールアドレスを入力してください', $email)
            ->expectsQuestion('パスワードを入力してください', $password)
            ->expectsOutput('開発者アカウントを作成しました:')
            ->expectsOutput("  メールアドレス: {$email}")
            ->assertExitCode(0);

        // ユーザーとアカウントが作成されているかチェック
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);

        $account = Account::where('user_id', $user->id)->first();
        $this->assertNotNull($account);
    }
}
