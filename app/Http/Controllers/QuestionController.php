<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Events\QuestionCreated;
use App\Events\QuestionDeleted;
use App\Events\QuestionUpdated;
use App\Http\Resources\QuestionResource;
use App\Language;
use App\Question;
use App\Services\LanguageService;
use App\States\Answer\Published as PublishedAnswer;
use App\States\Answer\Translated as TranslatedAnswer;
use App\States\Question\Published as PublishedQuestion;
use App\States\Question\QuestionState;
use App\States\Question\Translated as TranslatedQuestion;
use App\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    /**
     * LanguageService instance.
     *
     * @var LanguageService
     */
    private $languageService;

    /**
     * Create new controller instance.
     *
     * @return void
     */
    public function __construct(LanguageService $languageService)
    {
        $this->middleware('auth')->except(['apiForLanguage', 'apiForLanguageAndTopic']);

        $this->languageService = $languageService;
    }

    /**
     * Process validated descriptions and turn those into data set to be used with languages relation.
     * Desctiptions with empty values are removed.
     *
     * @param  array  $data
     * @return Collection
     */
    private function processDescriptions(array $data): Collection
    {
        return collect($data)
        ->keyBy(function ($single) {
            return $this->languageService->getLanguageIdByCode($single['code']);
        })
        ->filter(function ($single) {
            return trim($single['value']) !== '';
        })
        ->map(function ($single) {
            return [
                'description' => $single['value'],
            ];
        });
    }

    /**
     * Respond with answers using QuestionResource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $this->authorize('viewAny', Question::class);

        return QuestionResource::collection(Question::with(['languages', 'topic', 'answer', 'pendingQuestion'])->get());
    }

    /**
     * Respond with question states.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function states()
    {
        $this->authorize('viewAny', Question::class);

        return Question::getStatesFor('status')->map(function ($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    /**
     * Create new Question and respond with QuestionResource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Question::class);

        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
            'topic' => 'sometimes|required|exists:App\Topic,id',
        ]);

        $question = new Question;
        $question->save();

        if ($request->has('topic')) {
            $question->topic()->associate(Topic::find($request->get('topic')))->save();
        }

        if ($request->has('answer')) {
            $question->answer()->associate(Answer::find($request->get('answer')))->save();
        }

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $question->languages()->attach($descriptions);

        broadcast(new QuestionCreated($question))->toOthers();

        return response()->json(new QuestionResource($question), 200);
    }

    /**
     * Update existing Question and respond with QuestionResource or code 422 if state transition is not allowed.
     *
     * @param  Request  $request
     * @param  Question  $question
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $states = Question::getStatesFor('status')->map(function ($state) {
            return $state::getMorphClass();
        });
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
            'topic' => 'sometimes|required|exists:App\Topic,id',
            'status' => [
                'sometimes',
                'required',
                Rule::in($states),
            ],
        ]);

        // Make sure that user is allowed to set status
        if ($request->has('status')) {
            $statusClass = QuestionState::resolveStateClass($request->get('status'));

            if (! $question->canTransitionTo($statusClass) && ! $question->status->is($statusClass)) {
                return response()->json([
                    'message' => 'Status transition is not allowed!',
                ], 422);
            }

            if ($question->canTransitionTo($statusClass)) {
                $question->status->transitionTo($statusClass);
            }
        }

        if ($request->has('topic')) {
            $question->topic()->associate(Topic::find($request->get('topic')))->save();
        } else {
            $question->topic()->dissociate()->save();
        }

        if ($request->has('answer')) {
            $question->answer()->associate(Answer::find($request->get('answer')))->save();
        } else {
            $question->answer()->dissociate()->save();
        }

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $question->languages()->syncWithoutDetaching($descriptions);

        broadcast(new QuestionUpdated($question->refresh()))->toOthers();

        return response()->json(new QuestionResource($question->refresh()), 200);
    }

    /**
     * Responds with published questions, with published answers that have both been translated to a given language.
     *
     * @param  Language  $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiForLanguage(Language $language)
    {
        $data = [];

        Question::with(['languages', 'topic', 'answer'])
        ->whereState('status', [TranslatedQuestion::class, PublishedQuestion::class])
        ->whereHas('languages', function (Builder $query) use ($language) {
            $query->where('id', '=', $language->id);
        })
        ->whereHas('answer', function (Builder $query) use ($language) {
            $query->whereState('status', [TranslatedAnswer::class, PublishedAnswer::class])
            ->whereHas('languages', function (Builder $query) use ($language) {
                $query->where('id', '=', $language->id);
            });
        })
        ->chunk(50, function ($questions) use ($language, &$data) {
            foreach ($questions as $question) {
                $data[] = [
                    'id' => $question->id,
                    'description' => $question->languages->keyBy('id')->get($language->id)->pivot->description,
                    'answer' => [
                        'id' => $question->answer->id,
                        'description' => $question->answer->languages->keyBy('id')->get($language->id)->pivot->description,
                        'status' => $question->answer->status,
                    ],
                    'topic' => $question->topic ? [
                        'id' => $question->topic->id,
                        'description' => $question->topic->description,
                    ] : null,
                    'status' => $question->status,
                ];
            }
        });

        return response()->json($data);
    }

    /**
     * Responds with published questions, with published answers that have been translated to a given language that belong to a given topic.
     *
     * @param  Language  $language
     * @param  Topic  $topic
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiForLanguageAndTopic(Language $language, Topic $topic)
    {
        $data = [];

        Question::with(['languages', 'topic', 'answer'])
        ->whereState('status', [TranslatedQuestion::class, PublishedQuestion::class])
        ->whereHas('languages', function (Builder $query) use ($language) {
            $query->where('id', '=', $language->id);
        })
        ->whereHas('answer', function (Builder $query) use ($language) {
            $query->whereState('status', [TranslatedAnswer::class, PublishedAnswer::class])
            ->whereHas('languages', function (Builder $query) use ($language) {
                $query->where('id', '=', $language->id);
            });
        })
        ->where('topic_id', '=', $topic->id)
        ->chunk(50, function ($questions) use ($language, &$data) {
            foreach ($questions as $question) {
                $data[] = [
                    'id' => $question->id,
                    'description' => $question->languages->keyBy('id')->get($language->id)->pivot->description,
                    'answer' => [
                        'id' => $question->answer->id,
                        'description' => $question->answer->languages->keyBy('id')->get($language->id)->pivot->description,
                        'status' => $question->answer->status,
                    ],
                    'topic' => [
                        'id' => $question->topic->id,
                        'description' => $question->topic->description,
                    ],
                    'status' => $question->status,
                ];
            }
        });

        return response()->json($data);
    }

    /**
     * Removes question from the system.
     *
     * @param  Question  $question
     * @return JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Question $question)
    {
        $this->authorize('delete', $question);

        $question->delete();

        broadcast(new QuestionDeleted($question))->toOthers();

        return response()->json(new QuestionResource($question), 200);
    }
}
