<?php

namespace Tests\Feature;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Tests\KnowledgeBaseTestCase;

class LoginPageTest extends KnowledgeBaseTestCase
{
    /**
     * Tests login page as an anonymous user.
     *
     * @return void
     */
    public function test_anonymous()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSeeText('Login');
    }

    /**
     * Tests login page as an authenticated user.
     *
     * @retrun void
     */
    public function test_authenticated()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/login');

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }

    /**
     * Tests successful login.
     *
     * @return void
     */
    public function test_login_success()
    {
        Event::fake();
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/home');

        Event::assertDispatched(Login::class);
    }

    /**
     * Tests failed login.
     *
     * @return void
     */
    public function test_login_fail()
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
    }
}
