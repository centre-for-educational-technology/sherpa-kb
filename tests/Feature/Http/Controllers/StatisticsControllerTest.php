<?php

namespace Tests\Feature\Http\Controllers;

use App\Answer;
use App\Language;
use App\Question;
use Illuminate\Support\Collection;
use Tests\KnowledgeBaseTestCase;

class StatisticsControllerTest extends KnowledgeBaseTestCase
{
    /**
     * Test index endpoint with anonymous user.
     *
     * @return void
     */
    public function test_anonymous_index()
    {
        $response = $this->get('/statistics');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test index endpoint with authenticated user.
     *
     * @return void
     */
    public function test_authenticated_index()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/statistics');

        $response->assertStatus(403);
    }

    /**
     * Test index endpoint with language expert.
     *
     * @return void
     */
    public function test_language_expert_index()
    {
        $user = $this->createLanguageExpert();

        $response = $this->actingAs($user)
            ->get('/statistics');

        $response->assertStatus(403);
    }

    /**
     * Test index endpoint with allowed roles with pre-generated answers and questions.
     *
     * @return void
     */
    public function test_index()
    {
        $english = Language::where('code', 'en')->first();

        Answer::getStatesFor('status')->each(function ($state) use ($english) {
            $answer = Answer::factory([
                'status' => $state::$name,
            ])
                ->hasAttached($english, ['description' => 'Description'])
                ->create();

            Question::getStatesFor('status')->each(function ($state) use ($english, $answer) {
                Question::factory([
                    'status' => $state::$name,
                    'answer_id' => $answer->id,
                ])
                    ->hasAttached($english, ['description' => 'Description'])
                    ->create();
            });
        });

        $users = new Collection([
            $this->createMasterExpert(),
            $this->createAdministrator(),
        ]);

        $users->each(function ($user) {
            $response = $this->actingAs($user)
                ->get('/statistics');

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'questions' => [
                    'count',
                    'translations',
                    'available' => [
                        '*' => ['count', 'code'],
                    ],
                ],
                'answers' => ['count', 'translations'],
            ]);
            $response->assertJson([
                'questions' => [
                    'count' => 9,
                    'translations' => [
                        [
                            'count' => 9,
                            'code' => 'en',
                        ],
                    ],
                    'available' => [
                        'en' => [
                            'count' => 4,
                            'code' => 'en',
                        ],
                    ],
                ],
                'answers' => [
                    'count' => 3,
                    'translations' => [
                        [
                            'count' => 3,
                            'code' => 'en',
                        ],
                    ],
                ],
            ]);
        });
    }
}
