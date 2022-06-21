<?php

namespace Tests\Feature;

use App\UserRole;
use Tests\KnowledgeBaseTestCase;

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

    /**
     * Tests that user language functiona as expected.
     *
     * @retrun void
     */
    public function test_language()
    {
        $user = $this->createUser();

        // Note that createUser helper method sets language to English by default and model itself allows an empty
        // value. This does not matter as all users are created through the UI, which always has language field.
        $this->assertEquals('en', $user->language->code);

        $user->language()->associate($this->getLanguageIdByCode('et'))->save();

        $this->assertEquals('et', $user->language->code);
    }
}
