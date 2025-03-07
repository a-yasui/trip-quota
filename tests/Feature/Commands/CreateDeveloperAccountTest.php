<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateDeveloperAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 開発者アカウントが正常に作成されることをテスト
     */
    public function test_developer_account_is_created_successfully(): void
    {
        $email = 'developer@example.com';
        $password = 'password123';

        // コマンド実行
        $this->artisan('developer:create-account', [
            '--email' => $email,
            '--passwd' => $password,
        ])
        ->expectsOutputToContain('開発者アカウントを作成しました')
        ->assertExitCode(0);

        // ユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'Developer',
        ]);

        // パスワードが正しくハッシュ化されていることを確認
        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * 既存のメールアドレスを指定するとエラーになることをテスト
     */
    public function test_error_when_email_already_exists(): void
    {
        // 既存ユーザーの作成
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        // 既存メールアドレスでコマンド実行
        $this->artisan('developer:create-account', [
            '--email' => $existingUser->email,
            '--passwd' => 'password123',
        ])
        ->expectsOutputToContain('このメールアドレスは既に使用されています')
        ->assertExitCode(1);

        // ユーザー数が増えていないことを確認
        $this->assertEquals(1, User::where('email', $existingUser->email)->count());
    }

    /**
     * メールアドレスを指定しないとエラーになることをテスト
     */
    public function test_error_when_email_is_not_provided(): void
    {
        $this->artisan('developer:create-account', [
            '--passwd' => 'password123',
        ])
        ->expectsOutputToContain('メールアドレスとパスワードは必須です')
        ->assertExitCode(1);
    }

    /**
     * パスワードを指定しないとエラーになることをテスト
     */
    public function test_error_when_password_is_not_provided(): void
    {
        $this->artisan('developer:create-account', [
            '--email' => 'developer@example.com',
        ])
        ->expectsOutputToContain('メールアドレスとパスワードは必須です')
        ->assertExitCode(1);
    }

    /**
     * 不正なメールアドレス形式を指定するとエラーになることをテスト
     */
    public function test_error_when_email_format_is_invalid(): void
    {
        $this->artisan('developer:create-account', [
            '--email' => 'invalid-email',
            '--passwd' => 'password123',
        ])
        ->expectsOutputToContain('メールアドレスの形式が正しくありません')
        ->assertExitCode(1);
    }
}
