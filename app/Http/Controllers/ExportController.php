<?php

namespace App\Http\Controllers;

use App\Language;
use App\Question;
use App\States\Question\Published as PublishdQuestion;
use App\States\Answer\Published as PublishedAnswer;
use Illuminate\Database\Eloquent\Builder;

class ExportController extends Controller
{
    /**
     * Responds with exportable corpus data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Language $language)
    {
        $data = [];

        Question::with(['languages', 'topic', 'answer'])
        ->whereState('status', PublishdQuestion::class)
        ->whereHas('languages', function(Builder $query) use ($language) {
            $query->where('id', '=', $language->id);
        })
        ->whereHas('answer', function(Builder $query) use ($language) {
            $query->whereState('status', PublishedAnswer::class)
            ->whereHas('languages', function(Builder $query) use ($language) {
                $query->where('id', '=', $language->id);
            });
        })
        ->chunk(50, function($questions) use ($language, &$data) {
            foreach($questions as $question) {
                $data[] = [
                    'question' => $question->languages->keyBy('id')->get($language->id)->pivot->description,
                    'answer' => $question->answer->languages->keyBy('id')->get($language->id)->pivot->description,
                    'topic' => $question->topic ? $question->topic->description : '',
                ];
            }
        });

        return response()->json($data);
    }
}
