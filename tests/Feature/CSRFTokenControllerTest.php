<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

class CSRFTokenControllerTest extends KnowledgeBaseTestCase
{

    use RefreshDatabase;

    /**
     * Test CSRF token refresh with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous()
    {
        $response = $this->post('/refresh_csrf_token');

        $response->assertStatus(403);
        $response->assertExactJson([]);
    }

    /**
     * Test CSRF token refresh with an authenticated user.
     *
     * @retrun void
     */
    public function test_authenticated()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->post('/refresh_csrf_token');

        $response->assertStatus(200);
        $response->assertExactJson([
            'csrfToken' => csrf_token(),
        ]);
    }

}