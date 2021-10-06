<?php

namespace Tests\Feature;

class HomePageTest extends KnowledgeBaseTestCase
{

    /**
     * Tests home page as an anonymous user.
     *
     * @return void
     */
    public function test_anonymous()
    {
        $response = $this->get('/home');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Tests home page as an authenticated user.
     *
     * @return void
     */
    public function test_authenticated()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/home');

        $response->assertStatus(200);
        $response->assertDontSeeText('Login');
        $response->assertSeeText('Logout');
        $response->assertSeeText('Hello, ' . $user->name);
        $response->assertSeeText('You do not have sufficient role to use the application. Please contact an administrator to have a role assigned.');
    }

    /**
     * Tests home page as a language expert user.
     *
     * @return void
     */
    public function test_language_expert()
    {
        $user = $this->createLanguageExpert();

        $response = $this->actingAs($user)
            ->get('/home');

        $response->assertStatus(200);
        $response->assertSeeText('Hello, ' . $user->name);
        $response->assertSeeText('Country SELFIE Expert for English');
        $response->assertSee('<app-sync :is-active="appSyncActive" :connection-state="connectionState"></app-sync>', false);
        $response->assertDontSee('<master-expert-view></master-expert-view>', false);
        $response->assertSee('<language-expert-view language="en"></language-expert-view>', false);
    }

    /**
     * Tests home page as a master expert user.
     *
     * @return void
     */
    public function test_master_expert()
    {
        $user = $this->createMasterExpert();

        $response = $this->actingAs($user)
            ->get('/home');

        $response->assertStatus(200);
        $response->assertSeeText('Hello, ' . $user->name);
        $response->assertSeeText('SELFIE master');
        $response->assertSee('<app-sync :is-active="appSyncActive" :connection-state="connectionState"></app-sync>', false);
        $response->assertSee('<master-expert-view></master-expert-view>', false);
        $response->assertDontSee('<language-expert-view language="en"></language-expert-view>', false);
    }

    /**
     * Tests home page as an administrator user.
     *
     * @return void
     */
    public function test_admin()
    {
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)
            ->get('/home');

        $response->assertStatus(200);
        $response->assertSeeText('Users');
        $response->assertSeeText('Hello, ' . $user->name);
        $response->assertSeeText('SELFIE master');
        $response->assertSee('<app-sync :is-active="appSyncActive" :connection-state="connectionState"></app-sync>', false);
        $response->assertSee('<master-expert-view></master-expert-view>', false);
        $response->assertDontSee('<language-expert-view language="en"></language-expert-view>', false);
    }

}
