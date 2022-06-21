<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Testing\TestResponse;
use Tests\KnowledgeBaseTestCase;

class CSRFTokenControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Makes necessary assertions for successful responses.
     *
     * @param  TestResponse  $response
     */
    protected function assertSuccessfulCsrfResponse(TestResponse $response)
    {
        $response->assertSuccessful();
        $response->assertExactJson([
            'csrfToken' => csrf_token(),
        ]);
    }

    /**
     * Test CSRF token refresh with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous()
    {
        $response = $this->post('/refresh_csrf_token');

        $this->assertSuccessfulCsrfResponse($response);
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

        $this->assertSuccessfulCsrfResponse($response);
    }

    /**
     * Test CSRF token refresh throttling middleware.
     *
     * @return void
     */
    public function test_throttle()
    {
        foreach (range(1, 11) as $index) {
            $response = $this->post('/refresh_csrf_token');

            $remaining = (10 - $index > 0) ? 10 - $index : 0;

            $response->assertHeader('x-ratelimit-limit', 10);
            $response->assertHeader('x-ratelimit-remaining', $remaining);

            if ($index < 11) {
                $this->assertSuccessfulCsrfResponse($response);
            } else {
                $response->assertStatus(429);
                $response->assertHeader('retry-after', 60);
            }
        }
    }
}
