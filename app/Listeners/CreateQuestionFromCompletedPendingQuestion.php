<?php

namespace App\Listeners;

use App\Events\QuestionCreated;
use App\PendingQuestion;
use App\Question;
use App\States\PendingQuestion\Completed;
use Spatie\ModelStates\Events\StateChanged;

class CreateQuestionFromCompletedPendingQuestion
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  StateChanged  $event
     * @return void
     */
    public function handle(StateChanged $event)
    {
        if ($event->model instanceof PendingQuestion && $event->model->status->is(Completed::class)) {
            $question = new Question;
            $question->save();
            $question->pendingQuestion()->associate($event->model)->save();
            $question->languages()->attach($event->model->languages->keyBy('id')->map(function ($item) {
                return [
                    'description' => $item->pivot->description,
                ];
            }));

            broadcast(new QuestionCreated($question))->toOthers();
        }
    }
}
