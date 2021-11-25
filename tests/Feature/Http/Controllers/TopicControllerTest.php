<?php

namespace Tests\Feature\Http\Controllers;

use App\Events\TopicCreated;
use App\Events\TopicDeleted;
use App\Events\TopicUpdated;
use App\Topic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Tests\KnowledgeBaseTestCase;

class TopicControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Count of topics created by seeder.
     *
     * @string
     */
    const SEEDED_TOPICS_COUNT = 25;

    /**
     * Structure of TopicResource JSON.
     *
     * @array
     */
    const JSON_RESOURCE_STRUCTURE = ['id', 'description', 'created_at', 'updated_at'];

    /**
     * Generates a collection of users that are not allowed to make modifications.
     *
     * @return Collection
     */
    private function generateForbiddenUsers(): Collection
    {
        return new Collection([
            $this->createUser(),
            $this->createLanguageExpert(),
        ]);
    }

    /**
     * Generates a collection of users that are allowed to make modifications.
     *
     * @return Collection
     */
    private function generateAllowedRoles(): Collection
    {
        return new Collection([
            $this->createMasterExpert(),
            $this->createAdministrator(),
        ]);
    }

    /**
     * Uses factory to create a topic.
     *
     * @return Topic
     */
    private function createTopic(): Topic
    {
        return Topic::factory()->create();
    }

    /**
     * Test API endpoint response.
     *
     * @return void
     */
    public function test_api()
    {
        $response = $this->get('/api/topics');

        $response->assertStatus(200);
        $response->assertJsonCount(self::SEEDED_TOPICS_COUNT);
        $response->assertJsonStructure([
            '*' => ['id', 'description'],
        ]);
    }

    public function test_anonymous_list()
    {
        $response = $this->get('/topics');

        $response->assertStatus(403);
    }

    public function test_authenticated_list()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)
            ->get('/topics');

        $response->assertStatus(403);
    }

    public function test_list()
    {
        $users = new Collection([
            $this->createLanguageExpert(),
            $this->createMasterExpert(),
            $this->createAdministrator(),
        ]);

        $users->each(function ($user) {
            $response = $this->actingAs($user)
                ->get('/topics');

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'data' => [
                    '*' => self::JSON_RESOURCE_STRUCTURE,
                ],
            ]);
        });
    }

    public function test_store_validation()
    {
        $user = $this->createLanguageExpert();

        $response = $this->actingAs($user)
            ->post('/answers');

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'descriptions' => 'The descriptions field is required.',
        ]);
    }

    public function test_anonymous_store()
    {
        $response = $this->post('/topics');

        $response->assertStatus(403);
    }

    public function test_forbidden_store()
    {
        $this->generateForbiddenUsers()->each(function ($user) {
            $response = $this->actingAs($user)
                ->post('/topics');

            $response->assertStatus(403);
        });
    }

    public function test_success_store()
    {
        Event::fake([
            TopicCreated::class,
        ]);

        $this->generateAllowedRoles()->each(function ($user) {
            $response = $this->actingAs($user)
                ->post('/topics', [
                    'description' => 'Description',
                ]);

            $response->assertStatus(200);
            $response->assertJsonStructure(self::JSON_RESOURCE_STRUCTURE);
        });

        Event::assertDispatchedTimes(TopicCreated::class, 2);
    }

    public function test_anonymous_update()
    {
        $topic = $this->createTopic();
        $response = $this->put('/topics/'.$topic->id);

        $response->assertStatus(403);
    }

    public function test_forbidden_update()
    {
        $this->generateForbiddenUsers()->each(function ($user) {
            $topic = $this->createTopic();
            $response = $this->actingAs($user)
                ->put('/topics/'.$topic->id);

            $response->assertStatus(403);
        });
    }

    public function test_success_update()
    {
        Event::fake([
            TopicUpdated::class,
        ]);

        $this->generateAllowedRoles()->each(function ($user) {
            $topic = $this->createTopic();
            $response = $this->actingAs($user)
                ->put('/topics/'.$topic->id, [
                    'description' => 'Description',
                ]);

            $response->assertStatus(200);
            $response->assertJsonStructure(self::JSON_RESOURCE_STRUCTURE);
        });

        Event::assertDispatchedTimes(TopicUpdated::class, 2);
    }

    public function test_anonymous_delete()
    {
        $topic = $this->createTopic();
        $response = $this->delete('/topics/'.$topic->id);

        $response->assertStatus(403);
    }

    public function test_forbidden_delete()
    {
        $this->generateForbiddenUsers()->each(function ($user) {
            $topic = $this->createTopic();
            $response = $this->actingAs($user)
                ->delete('/topics/'.$topic->id);

            $response->assertStatus(403);
        });
    }

    public function test_success_delete()
    {
        Event::fake([
            TopicDeleted::class,
        ]);

        $this->generateAllowedRoles()->each(function ($user) {
            $topic = $this->createTopic();
            $response = $this->actingAs($user)
                ->delete('/topics/'.$topic->id, [
                    'description' => 'Description',
                ]);

            $response->assertStatus(200);
            $response->assertJsonStructure(self::JSON_RESOURCE_STRUCTURE);
        });

        Event::assertDispatchedTimes(TopicDeleted::class, 2);
    }
}
