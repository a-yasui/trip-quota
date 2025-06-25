<?php

namespace App\Console\Commands\Admin;

use Illuminate\Console\Command;
use App\Models\Admin;

class DeleteAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:delete {email : Admin email to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an admin user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $admin = Admin::where('email', $email)->first();
        
        if (!$admin) {
            $this->error("Admin with email '{$email}' not found.");
            return Command::FAILURE;
        }
        
        if (!$this->confirm("Are you sure you want to delete admin '{$admin->name}' ({$admin->email})?")) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }
        
        try {
            $adminName = $admin->name;
            $adminEmail = $admin->email;
            $admin->delete();
            
            $this->info("Admin user '{$adminName}' ({$adminEmail}) deleted successfully.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to delete admin user: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
