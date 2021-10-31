<?php

namespace Tests\Feature;

class HelperResponseUserRatingControllerTest extends KnowledgeBaseTestCase
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
    const TABLE_NAME = 'helper_response_user_ratings';

    /**
     * Store action URL.
     *
     * @string
     */
    const STORE_URL = '/api/helper_response_user_ratings';

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
            'answer' => 'The answer field is required.',
            'languageCode' => 'The language code field is required.',
            'rating' => 'The rating field is required.',
        ]);

        $response = $this->post(self::STORE_URL, [
            'question' => 'Question',
            'answer' => 'Answer',
            'languageCode' => 'zz',
            'rating' => 'zz',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'languageCode' => 'The selected language code is invalid.',
            'rating' => 'The rating must be between 1 and 3 digits.',
        ]);
    }

    /**
     * Test store action.
     *
     * @return void
     */
    public function test_store()
    {
        $response = $this->post(self::STORE_URL, [
            'question' => 'Question',
            'answer' => 'Answer',
            'languageCode' => 'en',
            'rating' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(self::JSON_RESPONSE);
        $this->assertDatabaseCount(self::TABLE_NAME, 1);
        $this->assertDatabaseHas(self::TABLE_NAME, [
            'question' => 'Question',
            'answer' => 'Answer',
            'language_code' => 'en',
            'rating' => 1,
            'ip' => '127.0.0.1',
        ]);
    }
}
