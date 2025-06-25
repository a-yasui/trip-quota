<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ShowUserCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user:show command displays users correctly
     */
    public function test_command_displays_users_correctly(): void
    {
        // Create test users
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        
        $this->artisan('user:show')
            ->expectsTable(
                ['id', 'email'],
                [
                    [$user1->id, $user1->email],
                    [$user2->id, $user2->email],
                ]
            )
            ->assertExitCode(0);
    }
    
    /**
     * Test user:show command when no users exist
     */
    public function test_command_shows_message_when_no_users(): void
    {
        $this->artisan('user:show')
            ->expectsOutput('No users found.')
            ->assertExitCode(0);
    }
}
