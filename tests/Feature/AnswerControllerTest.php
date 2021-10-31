<?php

namespace Tests\Feature;

use App\Answer;
use App\Events\AnswerCreated;
use App\Events\AnswerDeleted;
use App\Events\AnswerUpdated;
use App\Language;
use App\States\Answer\InTranslation;
use App\States\Answer\Translated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class AnswerControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Answers list JSON response.
     *
     * @var array
     */
    const LIST_JSON_RESPONSE = [
        'data' => [],
    ];

    /**
     * Answer states JSON response.
     *
     * @var array
     */
    const STATES_JSON_RESPONSE = [
        [
            'value' => 'in_translation',
            'text' => 'inTranslation',
        ],
        [
            'value' => 'published',
            'text' => 'Published',
        ],
        [
            'value' => 'translated',
            'text' => 'Translated',
        ],
    ];

    /**
     * Returns a collection of users with all roles defined in the system.
     *
     * @return Collection
     */
    private function generateUsersOfAllRoles(): Collection
    {
        return new Collection([
            $this->createLanguageExpert(),
            $this->createMasterExpert(),
            $this->createAdministrator(),
        ]);
    }

    /**
     * Tests answers list with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_list()
    {
        $response = $this->get('/answers');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Tests answers list with an authenticated user.
     *
     * @return void
     */
    public function test_authenticated_list()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/answers');

        $response->assertStatus(403);
    }

    /**
     * Tests answers list endpoint with roles that are allowed to access it.
     *
     * @retrun void
     */
    public function test_list()
    {
        $this->generateUsersOfAllRoles()->each(function ($user) {
            $response = $this->actingAs($user)
                ->get('/answers');

            $response->assertStatus(200);
            $response->assertExactJson(self::LIST_JSON_RESPONSE);
        });
    }

    /**
     * Tests answer states with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_states()
    {
        $response = $this->get('/answers/states');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Tests answers states with an authenticated user.
     *
     * @return void
     */
    public function test_authenticated_states()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/answers/states');

        $response->assertStatus(403);
    }

    /**
     * Tests states endpoint with roles that are allowed to access it.
     *
     * @return void
     */
    public function test_states()
    {
        $this->generateUsersOfAllRoles()->each(function ($user) {
            $response = $this->actingAs($user)
                ->get('/answers/states');

            $response->assertStatus(200);
            $response->assertExactJson(self::STATES_JSON_RESPONSE);
        });
    }

    /**
     * Tests store action with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_store()
    {
        $response = $this->post('/answers');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Tests store action with an authenticated user.
     *
     * @returns void
     */
    public function test_authenticated_store()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->post('/answers');

        $response->assertStatus(403);
    }

    /**
     * Tests store action validation rules.
     *
     * @return void
     */
    public function test_store_validation()
    {
        $user = $this->createLanguageExpert();

        $data = new Collection([
            [
                'data' => [],
                'expectedErrors' => [
                    'descriptions' => 'The descriptions field is required.',
                ],
            ],
            [
                'data' => [
                    'descriptions' => [
                        [],
                    ],
                ],
                'expectedErrors' => [
                    'descriptions.0.code' => 'The descriptions.0.code field is required.',
                    'descriptions.0.value' => 'The descriptions.0.value field is required.',
                ],
            ],
            [
                'data' => [
                    'descriptions' => [
                        [
                            'code' => 'zz',
                            'value' => 'Description',
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'descriptions.0.code' => 'The selected descriptions.0.code is invalid.',
                ],
            ],
        ]);

        $data->each(function ($item, $index) use ($user) {
            $response = $this->actingAs($user)
                ->post('/answers', $item['data']);

            $response->assertStatus(302);
            $response->assertSessionHasErrors($item['expectedErrors']);
        });
    }

    /**
     * Test store action with roles that are allowed to access it and setting as translated.
     *
     * @return void
     */
    public function test_store_with_set_translated()
    {
        Event::fake([
            AnswerCreated::class,
        ]);

        $this->generateUsersOfAllRoles()->each(function ($user) {
            $response = $this->actingAs($user)
                ->post('/answers', [
                    'descriptions' => [
                        [
                            'code' => 'en',
                            'value' => 'Answer text',
                        ],
                    ],
                    'setTranslated' => true,
                ]);

            $response->assertStatus(200);
            $response->assertJson([
                'descriptions' => [
                    'en' => 'Answer text',
                ],
                'status' => [
                    'value' => Translated::$name,
                    'status' => Translated::status(),
                ],
            ]);
        });

        Event::assertDispatchedTimes(AnswerCreated::class, 3);
    }

    /**
     * Test store action with roles that are allowed to access it and not setting as translated.
     *
     * @return void
     */
    public function test_store_without_set_translated()
    {
        Event::fake([
            AnswerCreated::class,
        ]);

        $this->generateUsersOfAllRoles()->each(function ($user) {
            $response = $this->actingAs($user)
                ->post('/answers', [
                    'descriptions' => [
                        [
                            'code' => 'en',
                            'value' => 'Answer text',
                        ],
                    ],
                ]);

            $response->assertStatus(200);
            $response->assertJson([
                'descriptions' => [
                    'en' => 'Answer text',
                ],
                'status' => [
                    'value' => InTranslation::$name,
                    'status' => InTranslation::status(),
                ],
            ]);
        });

        Event::assertDispatchedTimes(AnswerCreated::class, 3);
    }

    /**
     * Tests update action with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_update()
    {
        $answer = Answer::factory()->create();

        $response = $this->put('/answers/'.$answer->id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Tests update action with an authenticated user.
     *
     * @returns void
     */
    public function test_authenticated_update()
    {
        $user = $this->createUser();
        $answer = Answer::factory()->create();

        $response = $this->actingAs($user)
            ->put('/answers/'.$answer->id);

        $response->assertStatus(403);
    }

    /**
     * Tests update action validation rules.
     *
     * @return void
     */
    public function test_update_validation()
    {
        $user = $this->createLanguageExpert();
        $answer = Answer::factory()->create();

        $data = new Collection([
            [
                'data' => [],
                'expectedErrors' => [
                    'descriptions' => 'The descriptions field is required.',
                ],
            ],
            [
                'data' => [
                    'descriptions' => [
                        [],
                    ],
                ],
                'expectedErrors' => [
                    'descriptions.0.code' => 'The descriptions.0.code field is required.',
                    'descriptions.0.value' => 'The descriptions.0.value field is required.',
                ],
            ],
            [
                'data' => [
                    'descriptions' => [
                        [
                            'code' => 'zz',
                            'value' => 'Description',
                        ],
                    ],
                    'status' => $answer->status->getValue(),
                ],
                'expectedErrors' => [
                    'descriptions.0.code' => 'The selected descriptions.0.code is invalid.',
                ],
            ],
            [
                'data' => [
                    'descriptions' => [
                        [
                            'code' => 'en',
                            'value' => 'Description',
                        ],
                    ],
                    'status' => 'wrong',
                ],
                'expectedErrors' => [
                    'status' => 'The selected status is invalid.',
                ],
            ],
        ]);

        $data->each(function ($item) use ($user, $answer) {
            $response = $this->actingAs($user)
                ->put('/answers/'.$answer->id, $item['data']);

            $response->assertStatus(302);
            $response->assertSessionHasErrors($item['expectedErrors']);
        });
    }

    /**
     * Test update action with roles that are allowed to access it and set status as translated.
     *
     * @return void
     */
    public function test_update()
    {
        Event::fake([
            AnswerUpdated::class,
        ]);

        $this->generateUsersOfAllRoles()->each(function ($user) {
            $answer = Answer::factory()->create();
            $response = $this->actingAs($user)
                ->put('/answers/'.$answer->id, [
                    'descriptions' => [
                        [
                            'code' => 'en',
                            'value' => 'Answer text',
                        ],
                    ],
                    'status' => Translated::$name,
                ]);

            $response->assertStatus(200);
            $response->assertJson([
                'descriptions' => [
                    'en' => 'Answer text',
                ],
                'status' => [
                    'value' => Translated::$name,
                    'status' => Translated::status(),
                ],
            ]);
        });

        Event::assertDispatchedTimes(AnswerUpdated::class, 3);
    }

    /**
     * Test update action with disallowed status transition.
     *
     * @return void
     */
    public function test_update_unsupported_status_transition()
    {
        $user = $this->createLanguageExpert();
        $answer = Answer::factory()->translated()->create();

        $response = $this->actingAs($user)
            ->put('/answers/'.$answer->id, [
                'descriptions' => [
                    [
                        'code' => 'en',
                        'value' => 'Answer text',
                    ],
                ],
                'status' => InTranslation::$name,
            ]);

        $response->assertStatus(422);
        $response->assertExactJson([
            'message' => 'Status transition is not allowed!',
        ]);
    }

    /**
     * Tests delete action with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_delete()
    {
        $answer = Answer::factory()->create();

        $response = $this->delete('/answers/'.$answer->id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Tests delete action with an authenticated user.
     *
     * @returns void
     */
    public function test_authenticated_delete()
    {
        $user = $this->createUser();
        $answer = Answer::factory()->create();

        $response = $this->actingAs($user)
            ->delete('/answers/'.$answer->id);

        $response->assertStatus(403);
    }

    /**
     * Test delete action with roles that are allowed to access it and status of in_translation.
     *
     * @return void
     */
    public function test_delete()
    {
        Event::fake([
            AnswerDeleted::class,
        ]);

        $this->generateUsersOfAllRoles()->each(function ($user) {
            $answer = Answer::factory()->create();
            $response = $this->actingAs($user)
                ->delete('/answers/'.$answer->id);

            $response->assertStatus(200);
            $response->assertJson([
                'descriptions' => [],
                'status' => [
                    'value' => $answer->status->getValue(),
                    'status' => $answer->status->status(),
                ],
            ]);
        });

        Event::assertDispatchedTimes(AnswerDeleted::class, 3);
    }

    /**
     * Test answers for language API response.
     *
     * @return void
     */
    public function test_api_for_language()
    {
        $response = $this->get('/api/answers/zz');

        $response->assertStatus(404);

        $response = $this->get('/api/answers/en');

        $response->assertStatus(200);
        $response->assertExactJson([]);

        $english = Language::where('code', 'en')->first();
        Answer::factory()
            ->translated()
            ->hasAttached($english, ['description' => 'Description'])
            ->create();
        Answer::factory()
            ->published()
            ->hasAttached($english, ['description' => 'Description'])
            ->create();

        $response = $this->get('/api/answers/en');
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'description' => 'Description',
        ]);
    }
}
