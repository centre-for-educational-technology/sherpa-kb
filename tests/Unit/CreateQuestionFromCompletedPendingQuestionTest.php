<?php

namespace Tests\Unit;

use App\Events\AnswerCreated;
use App\Events\QuestionCreated;
use App\Language;
use App\Listeners\CreateQuestionFromCompletedPendingQuestion;
use App\PendingQuestion;
use App\States\PendingQuestion\Canceled;
use App\States\PendingQuestion\Completed;
use App\States\PendingQuestion\Propagated;
use App\States\Question\InTranslation;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Events\StateChanged;
use Tests\TestCase;

class CreateQuestionFromCompletedPendingQuestionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests that event listener is attached to an event.
     *
     * @return void
     */
    public function test_is_attached_to_event()
    {
        Event::fake([
            StateChanged::class,
        ]);
        Event::assertListening(
            StateChanged::class,
            CreateQuestionFromCompletedPendingQuestion::class
        );
    }

    /**
     * Tests that question gets created on transition to status of Completed.
     *
     * @returns void
     */
    public function test_it_creates_question()
    {
        $this->seed(LanguageSeeder::class);

        Event::fake([
            QuestionCreated::class,
        ]);

        $english = Language::where('code', 'en')->first();

        $pendingQuestion = PendingQuestion::factory([
            'status' => Propagated::$name,
        ])
            ->hasAttached($english, ['description' => 'Description'])
            ->create();
        $pendingQuestion->status->transitionTo(Completed::class);

        $this->assertDatabaseCount('pending_questions', 1);
        $this->assertDatabaseHas('pending_questions', [
            'status' => Completed::$name,
        ]);
        $this->assertDatabaseCount('language_pending_question', 1);
        $this->assertDatabaseHas('language_pending_question', [
            'pending_question_id' => $pendingQuestion->id,
            'language_id' => $english->id,
            'description' => 'Description',
        ]);
        $this->assertDatabaseCount('questions', 1);
        $this->assertDatabaseHas('questions', [
            'status' => InTranslation::$name,
            'pending_question_id' => $pendingQuestion->id,
        ]);
        $this->assertDatabaseCount('language_question', 1);
        $this->assertDatabaseHas('language_question', [
            'language_id' => $english->id,
            'description' => 'Description',
        ]);

        Event::assertDispatched(QuestionCreated::class);
    }

    /**
     * Tests that question does not get created on transition to status of Canceled.
     *
     * @returns void
     */
    public function test_it_does_not_create_question()
    {
        $this->seed(LanguageSeeder::class);

        Event::fake([
            AnswerCreated::class,
        ]);

        $english = Language::where('code', 'en')->first();

        $pendingQuestion = PendingQuestion::factory([
            'status' => Propagated::$name,
        ])
            ->hasAttached($english, ['description' => 'Description'])
            ->create();
        $pendingQuestion->status->transitionTo(Canceled::class);

        $this->assertDatabaseCount('pending_questions', 1);
        $this->assertDatabaseHas('pending_questions', [
            'status' => Canceled::$name,
        ]);
        $this->assertDatabaseCount('language_pending_question', 1);
        $this->assertDatabaseHas('language_pending_question', [
            'pending_question_id' => $pendingQuestion->id,
            'language_id' => $english->id,
            'description' => 'Description',
        ]);
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseMissing('questions', [
            'status' => InTranslation::$name,
            'pending_question_id' => $pendingQuestion->id,
        ]);
        $this->assertDatabaseCount('language_question', 0);
        $this->assertDatabaseMissing('language_question', [
            'language_id' => $english->id,
            'description' => 'Description',
        ]);

        Event::assertNotDispatched(QuestionCreated::class);
    }
}
