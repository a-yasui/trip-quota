<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminCommandsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin:add command creates admin successfully
     */
    public function test_admin_add_command_creates_admin_successfully(): void
    {
        $this->artisan('admin:add', [
            '--name' => 'Test Admin',
            '--email' => 'admin@example.com',
            '--password' => 'password123',
        ])
        ->expectsOutput("Admin user 'Test Admin' (admin@example.com) created successfully.")
        ->assertExitCode(0);

        $this->assertDatabaseHas('admins', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
        ]);

        $admin = Admin::where('email', 'admin@example.com')->first();
        $this->assertTrue(Hash::check('password123', $admin->password));
    }

    /**
     * Test admin:add command fails with duplicate email
     */
    public function test_admin_add_command_fails_with_duplicate_email(): void
    {
        Admin::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:add', [
            '--name' => 'Test Admin',
            '--email' => 'admin@example.com',
            '--password' => 'password123',
        ])
        ->expectsOutput('The email has already been taken.')
        ->assertExitCode(1);
    }

    /**
     * Test admin:add command fails with invalid password
     */
    public function test_admin_add_command_fails_with_invalid_password(): void
    {
        $this->artisan('admin:add', [
            '--name' => 'Test Admin',
            '--email' => 'admin@example.com',
            '--password' => 'short',
        ])
        ->expectsOutput('The password must be at least 8 characters.')
        ->assertExitCode(1);
    }

    /**
     * Test admin:delete command deletes admin successfully
     */
    public function test_admin_delete_command_deletes_admin_successfully(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:delete', ['email' => 'admin@example.com'])
        ->expectsConfirmation("Are you sure you want to delete admin '{$admin->name}' ({$admin->email})?", 'yes')
        ->expectsOutput("Admin user '{$admin->name}' ({$admin->email}) deleted successfully.")
        ->assertExitCode(0);

        $this->assertDatabaseMissing('admins', [
            'email' => 'admin@example.com',
        ]);
    }

    /**
     * Test admin:delete command fails with non-existent email
     */
    public function test_admin_delete_command_fails_with_non_existent_email(): void
    {
        $this->artisan('admin:delete', ['email' => 'nonexistent@example.com'])
        ->expectsOutput("Admin with email 'nonexistent@example.com' not found.")
        ->assertExitCode(1);
    }

    /**
     * Test admin:change-password command changes password successfully
     */
    public function test_admin_change_password_command_changes_password_successfully(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@example.com']);
        $newPassword = 'newpassword123';

        $this->artisan('admin:change-password', ['email' => 'admin@example.com'])
        ->expectsQuestion('New password', $newPassword)
        ->expectsOutput("Password for admin '{$admin->name}' ({$admin->email}) changed successfully.")
        ->assertExitCode(0);

        $admin->refresh();
        $this->assertTrue(Hash::check($newPassword, $admin->password));
    }

    /**
     * Test admin:change-password command fails with non-existent email
     */
    public function test_admin_change_password_command_fails_with_non_existent_email(): void
    {
        $this->artisan('admin:change-password', ['email' => 'nonexistent@example.com'])
        ->expectsOutput("Admin with email 'nonexistent@example.com' not found.")
        ->assertExitCode(1);
    }

    /**
     * Test admin:change-password command fails with invalid password
     */
    public function test_admin_change_password_command_fails_with_invalid_password(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:change-password', ['email' => 'admin@example.com'])
        ->expectsQuestion('New password', 'short')
        ->expectsOutput('The password must be at least 8 characters.')
        ->assertExitCode(1);
    }
}
