<?php

namespace Tests\Feature;

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
        ]
    ];

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
     * Tests answers list with a language expert user.
     *
     * @return void
     */
    public function test_language_expert_list()
    {
        $user = $this->createLanguageExpert();

        $response = $this->actingAs($user)
            ->get('/answers');

        $response->assertStatus(200);
        $response->assertExactJson(self::LIST_JSON_RESPONSE);
    }

    /**
     * Tests answers list with a master expert user.
     *
     * @return void
     */
    public function test_master_expert_list()
    {
        $user = $this->createMasterExpert();

        $response = $this->actingAs($user)
            ->get('/answers');

        $response->assertStatus(200);
        $response->assertExactJson(self::LIST_JSON_RESPONSE);
    }

    /**
     * Tests answers list with an administrator user.
     *
     * @return void
     */
    public function test_admin_list()
    {
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)
            ->get('/answers');

        $response->assertStatus(200);
        $response->assertExactJson(self::LIST_JSON_RESPONSE);
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
     * Tests answers states with a language expert user.
     *
     * @return void
     */
    public function test_language_expert_states()
    {
        $user = $this->createLanguageExpert();

        $response = $this->actingAs($user)
            ->get('/answers/states');

        $response->assertStatus(200);
        $response->assertExactJson(self::STATES_JSON_RESPONSE);
    }

    /**
     * Tests answers states with a master expert user.
     *
     * @return void
     */
    public function test_master_expert_states()
    {
        $user = $this->createMasterExpert();

        $response = $this->actingAs($user)
            ->get('/answers/states');

        $response->assertStatus(200);
        $response->assertExactJson(self::STATES_JSON_RESPONSE);
    }

    /**
     * Tests answers states with an administrator user.
     *
     * @return void
     */
    public function test_admin_states()
    {
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)
            ->get('/answers/states');

        $response->assertStatus(200);
        $response->assertExactJson(self::STATES_JSON_RESPONSE);
    }

    // TODO Test store, update, delete, apiForLanguage

}
