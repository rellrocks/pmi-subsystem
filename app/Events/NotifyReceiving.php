<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotifyReceiving extends Event
{
    use SerializesModels;
    public $mr_ids;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $ids)
    {
        $this->mr_ids = $ids;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return $this->mr_ids;
    }
}
