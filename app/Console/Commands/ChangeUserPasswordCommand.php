<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangeUserPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change {user_id : The ID of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change password for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }
        
        $password = $this->secret('password');
        
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
        
        $user->password = Hash::make($password);
        $user->save();
        
        $this->info("Password for user ID {$userId} ({$user->email}) has been changed successfully.");
        
        return Command::SUCCESS;
    }
}
