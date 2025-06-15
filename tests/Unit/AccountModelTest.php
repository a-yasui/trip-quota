<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_can_be_created_with_valid_data()
    {
        $user = User::factory()->create();

        $account = Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'testuser',
            'display_name' => 'Test User',
        ]);

        $this->assertDatabaseHas('accounts', [
            'account_name' => 'testuser',
            'display_name' => 'Test User',
        ]);

        $this->assertInstanceOf(Account::class, $account);
    }

    public function test_account_belongs_to_user()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $account->user);
        $this->assertEquals($user->id, $account->user->id);
    }

    public function test_validate_account_name_with_valid_names()
    {
        $validNames = [
            'user123',
            'test_user',
            'user-name',
            'a123',
            'UserName',
        ];

        foreach ($validNames as $name) {
            $this->assertTrue(
                Account::validateAccountName($name),
                "Account name '{$name}' should be valid"
            );
        }
    }

    public function test_validate_account_name_with_invalid_names()
    {
        $invalidNames = [
            '123user',     // 数字で始まる
            'ab',          // 短すぎる
            'user name',   // スペース含む
            'user@name',   // 特殊文字含む
            '',            // 空文字
            'user.name',   // ドット含む
        ];

        foreach ($invalidNames as $name) {
            $this->assertFalse(
                Account::validateAccountName($name),
                "Account name '{$name}' should be invalid"
            );
        }
    }

    public function test_find_by_account_name_ignore_case()
    {
        $user = User::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'TestUser',
        ]);

        // 大文字小文字を区別しない検索
        $account = Account::findByAccountNameIgnoreCase('testuser');
        $this->assertNotNull($account);
        $this->assertEquals('TestUser', $account->account_name);

        $account = Account::findByAccountNameIgnoreCase('TESTUSER');
        $this->assertNotNull($account);
        $this->assertEquals('TestUser', $account->account_name);
    }

    public function test_is_account_name_available()
    {
        $user = User::factory()->create();
        Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'existinguser',
        ]);

        // 既存のアカウント名（大文字小文字区別なし）
        $this->assertFalse(Account::isAccountNameAvailable('existinguser'));
        $this->assertFalse(Account::isAccountNameAvailable('ExistingUser'));
        $this->assertFalse(Account::isAccountNameAvailable('EXISTINGUSER'));

        // 新しいアカウント名
        $this->assertTrue(Account::isAccountNameAvailable('newuser'));
    }

    public function test_is_account_name_available_with_exclude_id()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id,
            'account_name' => 'testuser',
        ]);

        // 自分自身のIDを除外して検証
        $this->assertTrue(Account::isAccountNameAvailable('testuser', $account->id));
        $this->assertTrue(Account::isAccountNameAvailable('TestUser', $account->id));
    }
}
