<?php

namespace Tests\Feature;

use App\UserRole;

class UserModelTest extends KnowledgeBaseTestCase
{

    /**
     * Tests roles relation using UserRole pivot class and role check methods.
     *
     * @return void
     */
    public function test_roles()
    {
        $user = $this->createUser();
        $languageExpert = $this->createLanguageExpert();
        $masterExpert = $this->createMasterExpert();
        $administrator = $this->createAdministrator();

        $this->assertEquals(UserRole::class, $user->roles()->getPivotClass());

        $this->assertFalse($user->isLanguageExpert());
        $this->assertFalse($user->isMasterExpert());
        $this->assertFalse($user->isAdministrator());

        $this->assertTrue($languageExpert->isLanguageExpert());
        $this->assertFalse($languageExpert->isMasterExpert());
        $this->assertFalse($languageExpert->isAdministrator());

        $this->assertFalse($masterExpert->isLanguageExpert());
        $this->assertTrue($masterExpert->isMasterExpert());
        $this->assertFalse($masterExpert->isAdministrator());

        $this->assertFalse($administrator->isLanguageExpert());
        $this->assertFalse($administrator->isMasterExpert());
        $this->assertTrue($administrator->isAdministrator());
    }

}
