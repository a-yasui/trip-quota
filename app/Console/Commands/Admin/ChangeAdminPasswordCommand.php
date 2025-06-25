<?php

namespace App\Console\Commands\Admin;

use Illuminate\Console\Command;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangeAdminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:change-password {email : Admin email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change password for an admin user';

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
        
        $password = $this->secret('New password');
        
        $validator = Validator::make(['password' => $password], [
            'password' => ['required', 'string', 'min:8', 'regex:/^[a-zA-Z0-9]+$/'],
        ], [
            'password.regex' => 'The password must contain only alphanumeric characters.',
            'password.min' => 'The password must be at least 8 characters.',
        ]);
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }
        
        try {
            $admin->password = Hash::make($password);
            $admin->save();
            
            $this->info("Password for admin '{$admin->name}' ({$admin->email}) changed successfully.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to change admin password: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
