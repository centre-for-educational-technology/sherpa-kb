<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\User;

class UserModelTest extends TestCase
{
    /**
     * Test user role constants.
     *
     * @return void
     */
    public function test_role_constants()
    {
        $this->assertEquals('expert', User::ROLE_LANGUAGE_EXPERT);
        $this->assertEquals('master', User::ROLE_MASTER_EXPERT);
        $this->assertEquals('administrator', User::ROLE_ADMINISTRATOR);
    }

    /**
     * Test user LogsActivity constants.
     *
     * @return void
     */
    public function test_logs_activity()
    {
        $user = new User();

        $this->assertClassHasStaticAttribute('logAttributes', User::class);
        $this->assertClassHasStaticAttribute('submitEmptyLogs', User::class);
        $this->assertNotTrue($user->shouldSubmitEmptyLogs());
        $this->assertClassHasStaticAttribute('logAttributesToIgnore', User::class);
        //$this->assertEquals(['password', 'remember_token',], $user->attributesToBeLogged());
        $this->assertClassHasStaticAttribute('ignoreChangedAttributes', User::class);
        $this->assertEquals(['remember_token',], $user->attributesToBeIgnored());
    }

    // TODO Test logging and configuration, test fillable attributes by mass assigning, test hidden attributes by
    // converting to an array, test casts by getting verification time and date, test language by changing and assigning
    // a value, test all the is* checks for roles.
}
