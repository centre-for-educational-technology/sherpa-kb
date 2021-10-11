<?php

namespace App\Events;

use App\Http\Resources\TopicResource;
use App\Topic;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Topic instance.
     *
     * @var /App/Topic
     */
    public $topic;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.Sync');
    }

    /**
     * Topic data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (new TopicResource($this->topic))->toArray(request());
    }
}
