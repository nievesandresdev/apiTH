<?php

namespace App\Events\Queries;

use App\Models\Stay;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReloadDataStayWebapp  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $stay;
    /**
     * Create a new event instance.
     */
    public function __construct(Stay $stay)
    {
        $this->stay = $stay;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('reload-data-stay-webapp.' . $this->stay->id);
    }

    public function broadcastWith()
    {
        return [
            'stay' => $this->stay,
        ];
    }
}
