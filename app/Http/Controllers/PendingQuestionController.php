<?php

namespace App\Http\Controllers;

use App\Events\PendingQuestionCreated;
use App\Events\PendingQuestionDeleted;
use App\Events\PendingQuestionUpdated;
use App\Http\Resources\PendingQuestionResource;
use App\Language;
use App\PendingQuestion;
use App\Rules\ReCaptcha;
use App\Services\LanguageService;
use App\States\PendingQuestion\Pending;
use App\States\PendingQuestion\PendingQuestionState;
use App\States\PendingQuestion\Propagated;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PendingQuestionController extends Controller
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
        $this->middleware('auth')->except('store');

        $this->languageService = $languageService;
    }

    /**
     * Returns main language unique identifier or the one for English if that is the only one being set.
     *
     * @param  PendingQuestion  $pendingQuestion
     * @return int
     */
    private function getMainLanguageId(PendingQuestion $pendingQuestion): int
    {
        if ($pendingQuestion->languages->count() === 1 && $pendingQuestion->languages->first()->code === 'en') {
            return $pendingQuestion->languages->first()->id;
        }

        return $pendingQuestion->languages()->where('code', '<>', 'en')->first()->id;
    }

    /**
     * Respond with answers using PendingQuestionResource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $this->authorize('viewAny', PendingQuestion::class);

        return PendingQuestionResource::collection(PendingQuestion::with('languages')->get());
    }

    /**
     * Respond with PendingQuestion states.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function states()
    {
        $this->authorize('viewAny', PendingQuestion::class);

        return PendingQuestion::getStatesFor('status')->map(function ($state) {
            return [
                'value' => $state::getMorphClass(),
                'text' => $state::status(),
            ];
        });
    }

    /**
     * Create new PendingQuestion.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'question' => 'required',
            'language' => 'required|exists:App\Language,code',
            'token' => ['required', new ReCaptcha('suggest', 0.5)],
        ]);

        $question = new PendingQuestion;
        $question->save();
        $question->languages()->attach(Language::where('code', $validatedData['language'])->first()->id, [
            'description' => $validatedData['question'],
        ]);

        broadcast(new PendingQuestionCreated($question->refresh()));

        return response()->json([
            'message' => 'OK',
        ]);
    }

    /**
     * Update existing PendingQuestion and respond with PendingQuestionResource or code 422 if state transition is not allowed.
     *
     * @param  Request  $request
     * @param  PendingQuestion  $pendingQuestion
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, PendingQuestion $pendingQuestion)
    {
        $this->authorize('update', $pendingQuestion);

        $states = PendingQuestion::getStatesFor('status')->map(function ($state) {
            return $state::getMorphClass();
        });
        $validatedData = $request->validate([
            'question' => 'required',
            'translation' => 'required',
            'status' => [
                'sometimes',
                'required',
                Rule::in($states),
            ],
            'group' => 'nullable|integer|min:0',
        ]);

        // TODO Need to make sure that current user is allowed to set status as propagted
        if ($request->has('propagate') && $request->get('propagate')) {
            $pendingQuestion->status->transitionTo(Propagated::class);
        }

        // Make sure that user is allowed to set status
        if ($request->has('status')) {
            $statusClass = PendingQuestionState::resolveStateClass($request->get('status'));

            if (! $pendingQuestion->canTransitionTo($statusClass) && ! $pendingQuestion->status->is($statusClass)) {
                return response()->json([
                    'message' => 'Status transition is not allowed!',
                ], 422);
            }

            if ($pendingQuestion->canTransitionTo($statusClass)) {
                $pendingQuestion->status->transitionTo($statusClass);
            }
        }

        // XXX Need to check permissions
        if ($request->has('question')
            && $request->has('translation')
            && $request->get('question')
            && $request->get('translation')) {
            $languageData = [];

            $mainLanguageId = $this->getMainLanguageId($pendingQuestion);
            $englishLanguageId = $this->languageService->getLanguageIdByCode('en');

            $languageData[$mainLanguageId] = ['description' => $validatedData['question']];

            if ($mainLanguageId !== $englishLanguageId) {
                $languageData[$englishLanguageId] = ['description' => $validatedData['translation']];
            }

            $pendingQuestion->languages()->sync($languageData);
        }

        if ($request->has('group')) {
            $pendingQuestion->group_no = $request->get('group');
            $pendingQuestion->save();
        }

        broadcast(new PendingQuestionUpdated($pendingQuestion->refresh()))->toOthers();

        return response()->json(new PendingQuestionResource($pendingQuestion->refresh()), 200);
    }

    /**
     * Removes pending question from the system.
     *
     * @param  PendingQuestion  $pendingQuestion
     * @return JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(PendingQuestion $pendingQuestion)
    {
        $this->authorize('delete', $pendingQuestion);

        $pendingQuestion->delete();

        broadcast(new PendingQuestionDeleted($pendingQuestion))->toOthers();

        return response()->json(new PendingQuestionResource($pendingQuestion), 200);
    }
}
