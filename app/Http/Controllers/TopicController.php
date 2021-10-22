<?php

namespace App\Http\Controllers;

use App\Events\TopicCreated;
use App\Events\TopicDeleted;
use App\Events\TopicUpdated;
use App\Http\Resources\TopicResource;
use App\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Responds with topics for API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function api(): JsonResponse
    {
        $topics = Topic::all()->map(function ($topic) {
            return [
                'id' => $topic->id,
                'description' => $topic->description,
            ];
        });

        return response()->json($topics);
    }

    /**
     * Returns a list of all topics.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function list()
    {
        $this->authorize('viewAny', Topic::class);

        return TopicResource::collection(Topic::all());
    }

    /**
     * Stored new topic in the database.
     *
     * @param  Request  $request
     * @return JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', Topic::class);

        $validatedData = $request->validate([
            'description' => 'required',
        ]);

        $topic = new Topic;
        $topic->description = $validatedData['description'];
        $topic->save();

        broadcast(new TopicCreated($topic))->toOthers();

        return response()->json(new TopicResource($topic), 200);
    }

    /**
     * Updated an alreayd existing topic.
     *
     * @param  Request  $request
     * @param  Topic  $topic
     * @return JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Topic $topic)
    {
        $this->authorize('update', $topic);

        $validatedData = $request->validate([
            'description' => 'required',
        ]);

        $topic->description = $validatedData['description'];
        $topic->save();

        broadcast(new TopicUpdated($topic))->toOthers();

        return response()->json(new TopicResource($topic->refresh()), 200);
    }

    /**
     * Removes a topic from the system.
     *
     * @param  Topic  $topic
     * @return JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Topic $topic)
    {
        $this->authorize('delete', $topic);

        $topic->delete();

        broadcast(new TopicDeleted($topic))->toOthers();

        return response()->json(new TopicResource($topic), 200);
    }
}
