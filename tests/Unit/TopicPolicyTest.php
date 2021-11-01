<?php

namespace Tests\Feature;

use App\Topic;

class TopicPolicyTest extends KnowledgeBaseTestCase
{
    /**
     * Test topic policy with an authenticated user.
     *
     * @return void
     */
    public function test_authenticated()
    {
        $user = $this->createUser();
        $topic = Topic::factory()->create();

        $this->assertNotTrue($user->can('viewAny', Topic::class));
        $this->assertNotTrue($user->can('view', $topic));
        $this->assertNotTrue($user->can('create', Topic::class));
        $this->assertNotTrue($user->can('update', $topic));
        $this->assertNotTrue($user->can('delete', $topic));
        $this->assertNotTrue($user->can('restore', $topic));
        $this->assertNotTrue($user->can('forceDelete', $topic));
    }

    /**
     * Test topic policy with a language expert user.
     *
     * @return void
     */
    public function test_language_expert()
    {
        $user = $this->createLanguageExpert();
        $topic = Topic::factory()->create();

        $this->assertTrue($user->can('viewAny', Topic::class));
        $this->assertTrue($user->can('view', $topic));
        $this->assertNotTrue($user->can('create', Topic::class));
        $this->assertNotTrue($user->can('update', $topic));
        $this->assertNotTrue($user->can('delete', $topic));
        $this->assertNotTrue($user->can('restore', $topic));
        $this->assertNotTrue($user->can('forceDelete', $topic));
    }

    /**
     * Test topic policy with a master expert user.
     *
     * @return void
     */
    public function test_master_expert()
    {
        $user = $this->createMasterExpert();
        $topic = Topic::factory()->create();

        $this->assertTrue($user->can('viewAny', Topic::class));
        $this->assertTrue($user->can('view', $topic));
        $this->assertTrue($user->can('create', Topic::class));
        $this->assertTrue($user->can('update', $topic));
        $this->assertTrue($user->can('delete', $topic));
        $this->assertNotTrue($user->can('restore', $topic));
        $this->assertNotTrue($user->can('forceDelete', $topic));
    }

    /**
     * Test topic policy with a master expert user.
     *
     * @return void
     */
    public function test_administrator()
    {
        $user = $this->createAdministrator();
        $topic = Topic::factory()->create();

        $this->assertTrue($user->can('viewAny', Topic::class));
        $this->assertTrue($user->can('view', $topic));
        $this->assertTrue($user->can('create', Topic::class));
        $this->assertTrue($user->can('update', $topic));
        $this->assertTrue($user->can('delete', $topic));
        $this->assertTrue($user->can('restore', $topic));
        $this->assertTrue($user->can('forceDelete', $topic));
    }
}
