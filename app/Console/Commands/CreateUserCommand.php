<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = new User();
        $this->info('Creating user...');
        $user->name = $this->ask('Enter your Name');
        $user->email = $this->ask('Enter your Email');
        $user->password = $this->ask('Enter your Password');
        $user->save();
        $this->info('User created successfully!');
    }
}
