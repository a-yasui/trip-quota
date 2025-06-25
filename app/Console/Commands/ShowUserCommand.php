<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ShowUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display a list of registered users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::select('id', 'email')->get();

        if ($users->isEmpty()) {
            $this->info('No users found.');
            return Command::SUCCESS;
        }

        $this->table(
            ['id', 'email'],
            $users->toArray()
        );

        return Command::SUCCESS;
    }
}
