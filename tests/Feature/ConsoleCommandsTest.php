<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Hash;

class ConsoleCommandsTest extends KnowledgeBaseTestCase
{
    /**
     * Test the auth:create-admin command.
     *
     * @return void
     */
    public function test_create_admin_command()
    {
        $this->artisan('auth:create-admin Administrator administrator@localhost password')
            ->expectsOutput('Created an account for Administrator')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'administrator@localhost',
        ]);

        $user = User::find(1);

        $this->assertEquals('Administrator', $user->name);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertEquals(1, $user->roles->count());
        $this->assertTrue($user->isAdministrator());
    }

    /**
     * Test the permission:make-administrator command.
     *
     * @return void
     */
    public function test_make_administrator_command()
    {
        $user = $this->createUser();

        $this->artisan('permission:make-administrator 1')
            ->expectsOutput("Administrator role was assigned to a user {$user->name}")
            ->assertExitCode(0);

        $this->assertTrue($user->isAdministrator());

        $this->artisan('permission:make-administrator 1')
            ->expectsOutput("User {$user->name} already has an administrator role!")
            ->assertExitCode(0);

        $this->artisan('permission:make-administrator 2')
            ->expectsOutput('Could not find a user with identifier of 2!')
            ->assertExitCode(1);
    }
}
