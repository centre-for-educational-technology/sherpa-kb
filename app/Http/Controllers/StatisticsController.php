<?php

namespace App\Http\Controllers;

use App\States\Answer\Published as PublishedAnswer;
use App\States\Answer\Translated as TranslatedAnswer;
use App\States\Question\Published as PublishedQuestion;
use App\States\Question\Translated as TranslatedQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Question;
use App\Answer;

class StatisticsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:master|administrator');
    }

    /**
     * Returns statistics data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = [
            'questions' => [
                'count' => Question::count(),
                'translations' => DB::table('language_question')
                    ->join('languages', 'languages.id', '=', 'language_question.language_id')
                    ->select(DB::raw('count(*) AS count, languages.code AS code'))
                    ->groupBy('languages.code')
                    ->get(),
            ],
            'answers' => [
                'count' => Answer::count(),
                'translations' => DB::table('answer_language')
                    ->join('languages', 'languages.id', '=', 'answer_language.language_id')
                    ->select(DB::raw('count(*) AS count, languages.code AS code'))
                    ->groupBy('languages.code')
                    ->get(),
            ],
        ];

        $languages = Language::all();

        foreach ($languages as $language) {
            $count = Question::with(['languages', 'topic', 'answer'])
                ->whereState('status', [TranslatedQuestion::class, PublishedQuestion::class])
                ->whereHas('languages', function(Builder $query) use ($language) {
                    $query->where('id', '=', $language->id);
                })
                ->whereHas('answer', function(Builder $query) use ($language) {
                    $query->whereState('status', [TranslatedAnswer::class, PublishedAnswer::class])
                        ->whereHas('languages', function(Builder $query) use ($language) {
                            $query->where('id', '=', $language->id);
                        });
                })->count();

            $data['questions']['available'] = [
                'count' => $count,
                'code' => $language->code,
            ];
        }

        return response()->json($data);
    }
}
