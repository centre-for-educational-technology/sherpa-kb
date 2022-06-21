<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\KnowledgeBaseTestCase;
use Tests\TestCase;

class LanguageControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Test api endpoint.
     *
     * @return void
     */
    public function test_api()
    {
        $response = $this->get('api/languages');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
        $response->assertJsonStructure([
            '*' => ['code', 'name'],
        ]);
    }
}
