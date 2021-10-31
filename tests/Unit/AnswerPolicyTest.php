<?php

namespace Tests\Feature;

use App\Answer;
use App\States\Answer\Published;
use App\States\Answer\Translated;

class AnswerPolicyTest extends KnowledgeBaseTestCase
{
    /**
     * Test answer policy with an authenticated user.
     *
     * @return void
     */
    public function test_authenticated()
    {
        $user = $this->createUser();
        $answer = Answer::factory()->create();

        $this->assertNotTrue($user->can('viewAny', Answer::class));
        $this->assertNotTrue($user->can('view', $answer));
        $this->assertNotTrue($user->can('create', Answer::class));
        $this->assertNotTrue($user->can('update', $answer));
        $this->assertNotTrue($user->can('delete', $answer));
        $this->assertNotTrue($user->can('restore', $answer));
        $this->assertNotTrue($user->can('forceDelete', $answer));
    }

    /**
     * Test answer policy with a language expert user.
     *
     * @return void
     */
    public function test_language_expert()
    {
        $user = $this->createLanguageExpert();
        $answer = Answer::factory()->create();

        $this->assertTrue($user->can('viewAny', Answer::class));
        $this->assertTrue($user->can('view', $answer));
        $this->assertTrue($user->can('create', Answer::class));
        $this->assertTrue($user->can('update', $answer));
        $this->assertTrue($user->can('delete', $answer));
        $this->assertNotTrue($user->can('restore', $answer));
        $this->assertNotTrue($user->can('forceDelete', $answer));

        $answer->status->transitionTo(Translated::class);
        $this->assertTrue($user->can('delete', $answer));

        $answer->status->transitionTo(Published::class);
        $this->assertNotTrue($user->can('delete', $answer));
    }

    /**
     * Test answer policy with a master expert user.
     *
     * @return void
     */
    public function test_master_expert()
    {
        $user = $this->createMasterExpert();
        $answer = Answer::factory()->create();

        $this->assertTrue($user->can('viewAny', Answer::class));
        $this->assertTrue($user->can('view', $answer));
        $this->assertTrue($user->can('create', Answer::class));
        $this->assertTrue($user->can('update', $answer));
        $this->assertTrue($user->can('delete', $answer));
        $this->assertNotTrue($user->can('restore', $answer));
        $this->assertNotTrue($user->can('forceDelete', $answer));

        $answer->status->transitionTo(Translated::class);
        $this->assertTrue($user->can('delete', $answer));

        $answer->status->transitionTo(Published::class);
        $this->assertTrue($user->can('delete', $answer));
    }

    /**
     * Test answer policy with a master expert user.
     *
     * @return void
     */
    public function test_administrator()
    {
        $user = $this->createAdministrator();
        $answer = Answer::factory()->create();

        $this->assertTrue($user->can('viewAny', Answer::class));
        $this->assertTrue($user->can('view', $answer));
        $this->assertTrue($user->can('create', Answer::class));
        $this->assertTrue($user->can('update', $answer));
        $this->assertTrue($user->can('delete', $answer));
        $this->assertTrue($user->can('restore', $answer));
        $this->assertTrue($user->can('forceDelete', $answer));
    }
}
