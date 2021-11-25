<?php

namespace Tests\Feature\Http\Controllers;

use Tests\KnowledgeBaseTestCase;

class ExportControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Test api export endpoint for English with no data in the database.
     */
    public function test_export_without_data()
    {
        $response = $this->get('/api/export/en');

        $response->assertSuccessful();
        $response->assertJsonCount(0);
        $response->assertJson([]);
    }

    /**
     * Test api export endpoint for English and Estonian with English data preset generated.
     */
    public function test_export_with_data()
    {
        $this->createAnswerAndQuestionData();

        $this->assertDatabaseCount('questions', 9);
        $this->assertDatabaseCount('answers', 3);

        $response = $this->get('/api/export/en');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            '*' => ['question', 'answer', 'topic'],
        ]);
        $response->assertJsonCount(4);
        $response->assertJson([
            [
                'question' => 'Description',
                'answer' => 'Description',
                'topic' => '',
            ],
            [
                'question' => 'Description',
                'answer' => 'Description',
                'topic' => '',
            ],
            [
                'question' => 'Description',
                'answer' => 'Description',
                'topic' => '',
            ],
            [
                'question' => 'Description',
                'answer' => 'Description',
                'topic' => '',
            ],
        ]);

        $response = $this->get('/api/export/et');

        $response->assertSuccessful();
        $response->assertJsonCount(0);
        $response->assertJson([]);
    }
}
