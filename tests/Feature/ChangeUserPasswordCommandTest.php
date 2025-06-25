<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangeUserPasswordCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user:change command changes password successfully
     */
    public function test_command_changes_password_successfully(): void
    {
        $user = User::factory()->create();
        $newPassword = 'password123';
        
        $this->artisan('user:change', ['user_id' => $user->id])
            ->expectsQuestion('password', $newPassword)
            ->expectsOutput("Password for user ID {$user->id} ({$user->email}) has been changed successfully.")
            ->assertExitCode(0);
        
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }
    
    /**
     * Test user:change command with non-existent user
     */
    public function test_command_fails_with_non_existent_user(): void
    {
        $this->artisan('user:change', ['user_id' => 999])
            ->expectsOutput('User with ID 999 not found.')
            ->assertExitCode(1);
    }
    
    /**
     * Test user:change command with password too short
     */
    public function test_command_fails_with_short_password(): void
    {
        $user = User::factory()->create();
        
        $this->artisan('user:change', ['user_id' => $user->id])
            ->expectsQuestion('password', 'pass')
            ->expectsOutput('The password must be at least 8 characters.')
            ->assertExitCode(1);
    }
    
    /**
     * Test user:change command with password containing special characters
     */
    public function test_command_fails_with_special_characters(): void
    {
        $user = User::factory()->create();
        
        $this->artisan('user:change', ['user_id' => $user->id])
            ->expectsQuestion('password', 'password@123')
            ->expectsOutput('The password must contain only alphanumeric characters.')
            ->assertExitCode(1);
    }
    
    /**
     * Test user:change command with empty password
     */
    public function test_command_fails_with_empty_password(): void
    {
        $user = User::factory()->create();
        
        $this->artisan('user:change', ['user_id' => $user->id])
            ->expectsQuestion('password', '')
            ->expectsOutput('The password field is required.')
            ->assertExitCode(1);
    }
}
