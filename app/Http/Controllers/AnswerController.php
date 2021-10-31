<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Events\AnswerCreated;
use App\Events\AnswerDeleted;
use App\Events\AnswerUpdated;
use App\Http\Resources\AnswerResource;
use App\Language;
use App\Services\LanguageService;
use App\States\Answer\AnswerState;
use App\States\Answer\Published;
use App\States\Answer\Translated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AnswerController extends Controller
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
        $this->middleware('auth')->except('apiForLanguage');

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
     * Respond with answers using AnswerResource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $this->authorize('viewAny', Answer::class);

        return AnswerResource::collection(Answer::with('languages')->get());
    }

    /**
     * Respond with answer states.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function states()
    {
        $this->authorize('viewAny', Answer::class);

        return Answer::getStatesFor('status')->map(function ($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    /**
     * Create new Answer and respond with AnswerResource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Answer::class);

        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
        ]);

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $answer = new Answer;
        $answer->save();
        $answer->languages()->attach($descriptions);

        if ($request->has('setTranslated')) {
            $answer->status->transitionTo(Translated::class);
        }

        broadcast(new AnswerCreated($answer->refresh()))->toOthers();

        return response()->json(new AnswerResource($answer->refresh()), 200);
    }

    /**
     * Update existing Answer and respond with AnswerResource or code 422 if state transition is not allowed.
     *
     * @param Request $request
     * @param Answer $answer
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     */
    public function update(Request $request, Answer $answer)
    {
        $this->authorize('update', $answer);

        $states = Answer::getStatesFor('status')->map(function ($state) {
            return $state::getMorphClass();
        });
        $validatedData = $request->validate([
            'descriptions' => 'required|array',
            'descriptions.*.code' => 'required|exists:App\Language,code',
            'descriptions.*.value' => 'required',
            'status' => [
                'sometimes',
                'required',
                Rule::in($states),
            ],
        ]);

        // Make sure that user is allowed to set status
        if ($request->has('status')) {
            $statusClass = AnswerState::resolveStateClass($validatedData['status']);

            if (! $answer->canTransitionTo($statusClass) && ! $answer->status->is($statusClass)) {
                return response()->json([
                    'message' => 'Status transition is not allowed!',
                ], 422);
            }

            if ($answer->canTransitionTo($statusClass)) {
                $answer->status->transitionTo($statusClass);
            }
        }

        $descriptions = $this->processDescriptions($validatedData['descriptions']);

        $answer->languages()->syncWithoutDetaching($descriptions);

        broadcast(new AnswerUpdated($answer->refresh()))->toOthers();

        return response()->json(new AnswerResource($answer->refresh()), 200);
    }

    /**
     * Responds with published answers that have been translated to a given language.
     *
     * @param  Language  $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiForLanguage(Language $language)
    {
        $data = [];

        Answer::with(['languages'])
        ->whereState('status', [Translated::class, Published::class])
        ->whereHas('languages', function (Builder $query) use ($language) {
            $query->where('id', '=', $language->id);
        })
        ->chunk(50, function ($answers) use ($language, &$data) {
            foreach ($answers as $answer) {
                $data[] = [
                    'id' => $answer->id,
                    'description' => $answer->languages->keyBy('id')->get($language->id)->pivot->description,
                    'status' => $answer->status,
                ];
            }
        });

        return response()->json($data);
    }

    /**
     * Removes answer from the system.
     *
     * @param  Answer  $answer
     * @return JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Answer $answer)
    {
        $this->authorize('delete', $answer);

        $answer->delete();

        broadcast(new AnswerDeleted($answer))->toOthers();

        return response()->json(new AnswerResource($answer), 200);
    }
}
