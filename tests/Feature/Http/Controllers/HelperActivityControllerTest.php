<?php

namespace Tests\Feature\Http\Controllers;

use Tests\KnowledgeBaseTestCase;

class HelperActivityControllerTest extends KnowledgeBaseTestCase
{
    /**
     * JSON response.
     *
     * @var array
     */
    const JSON_RESPONSE = [
        'message' => 'OK',
    ];

    /**
     * Database table name.
     *
     * @string
     */
    const TABLE_NAME = 'helper_activity_log';

    /**
     * Store action URL.
     *
     * @string
     */
    const STORE_URL = '/api/helper_activity';

    /**
     * Test validation rules.
     *
     * @return void
     */
    public function test_store_validation()
    {
        $response = $this->post(self::STORE_URL);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'question' => 'The question field is required.',
            'languageCode' => 'The language code field is required.',
        ]);

        $response = $this->post(self::STORE_URL, [
            'question' => 'Question',
            'answer' => 'Answer',
            'languageCode' => 'zz',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'languageCode' => 'The selected language code is invalid.',
        ]);
    }

    /**
     * Test store action with no answer value provided.
     *
     * @return void
     */
    public function test_store_with_no_answer()
    {
        $response = $this->post(self::STORE_URL, [
            'question' => 'Question',
            'languageCode' => 'en',
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(self::JSON_RESPONSE);
        $this->assertDatabaseCount(self::TABLE_NAME, 1);
        $this->assertDatabaseHas(self::TABLE_NAME, [
            'question' => 'Question',
            'answer' => '',
            'language_code' => 'en',
            'ip' => '127.0.0.1',
        ]);
    }

    /**
     * Test store action with an answer value provided.
     *
     * @return void
     */
    public function test_store_with_answer()
    {
        $response = $this->post(self::STORE_URL, [
            'question' => 'Question',
            'answer' => 'Answer',
            'languageCode' => 'en',
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'OK',
        ]);
        $this->assertDatabaseCount(self::TABLE_NAME, 1);
        $this->assertDatabaseHas(self::TABLE_NAME, [
            'question' => 'Question',
            'answer' => 'Answer',
            'language_code' => 'en',
            'ip' => '127.0.0.1',
        ]);
    }
}
