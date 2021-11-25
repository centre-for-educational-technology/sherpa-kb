<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\KnowledgeBaseTestCase;
use Tests\TestCase;

class UserControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Generate collection of user roles that are forbidden to manage users.
     *
     * @return Collection
     */
    private function generateForbiddenUsers(): Collection
    {
        return new Collection([
            $this->createUser(),
            $this->createLanguageExpert(),
            $this->createMasterExpert(),
        ]);
    }

    /**
     * Test index with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_index()
    {
        $response = $this->get('/users');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test index with forbidden roles.
     *
     * @return void
     */
    public function test_forbidden_index()
    {
        $this->generateForbiddenUsers()->each(function ($user) {
            $response = $this->actingAs($user)
                ->get('/users');

            $response->assertStatus(403);
        });
    }

    /**
     * Test index with an administrator.
     *
     * @return void
     */
    public function test_index()
    {
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)
            ->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users');

        $response = $this->actingAs($user)
            ->getJson('/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'language',
                    'roles' => [
                        '*' => ['id', 'name'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test roles with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_roles()
    {
        $response = $this->get('/users/roles');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test roles with forbidden roles.
     *
     * @return void
     */
    public function test_forbidden_roles()
    {
        $this->generateForbiddenUsers()->each(function ($user) {
            $response = $this->actingAs($user)
                ->get('/users/roles');

            $response->assertStatus(403);
        });
    }

    /**
     * Test roles with an administrator.
     *
     * @return void
     */
    public function test_roles()
    {
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)
            ->get('/users/roles');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
        ]);
    }

    /**
     * Test delete with an anonymous user.
     *
     * @return void
     */
    public function test_anonymous_delete()
    {
        $user = $this->createUser();
        $response = $this->delete('/users/'.$user->id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test delete with forbidden roles.
     *
     * @return void
     */
    public function test_forbidden_delete()
    {
        $deletedUser = $this->createUser();

        $this->generateForbiddenUsers()->each(function ($user) use ($deletedUser) {
            $response = $this->actingAs($user)
                ->delete('/users/'.$deletedUser->id);

            $response->assertStatus(403);
        });
    }

    /**
     * Test delete with an administrator.
     *
     * @return void
     */
    public function test_delete()
    {
        $deletedUser = $this->createUser();
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)
            ->delete('/users/'.$deletedUser->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'language',
                'roles' => [
                    '*' => ['id', 'name'],
                ],
            ],
        ]);

        $response = $this->actingAs($user)
            ->delete('/users/'.$user->id);

        $response->assertStatus(422);
        $response->assertExactJson([
            'message' => 'You can not delete yourself!',
        ]);
    }

    // TODO Test store and update
}
