<?php

namespace Tests\Feature;

class FrontPageTest extends KnowledgeBaseTestCase
{
    /**
     * Tests front page as an anonymous user.
     *
     * @return void
     */
    public function test_anonymous()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('SHERPA Knowledge Base');
        $response->assertSeeText('Login');
        $response->assertDontSeeText('Register');
    }

    /**
     * Tests front page as an authenticated user.
     *
     * @return void
     */
    public function test_authenticated()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }
}
