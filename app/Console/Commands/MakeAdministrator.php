<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class MakeAdministrator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:make-administrator {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign an administrator role to a user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $role = 'administrator';
        $id = $this->argument('user');
        $user = User::find($id);

        if (! $user) {
            $this->error("Could not find a user with identifier of {$id}!");

            return 1;
        }

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
            $this->info("Administrator role was assigned to a user {$user->name}");

            return 0;
        }

        $this->line("User {$user->name} already has an administrator role!");

        return 0;
    }
}
