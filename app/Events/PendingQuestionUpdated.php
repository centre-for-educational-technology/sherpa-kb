<?php

namespace App\Events;

use App\Http\Resources\PendingQuestionResource;
use App\PendingQuestion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PendingQuestionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * PendingQuestion instance.
     *
     * @var /App/PendingQuestion
     */
    public $pendingQuestion;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PendingQuestion $pendingQuestion)
    {
        $this->pendingQuestion = $pendingQuestion;
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
     * PendingQuestion data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (new PendingQuestionResource($this->pendingQuestion))->toArray(request());
    }
}
