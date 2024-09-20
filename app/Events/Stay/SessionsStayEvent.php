<?php

namespace App\Events\Stay;

use App\Models\Stay;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionsStayEvent  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $stay;
    public $data;
    /**
     * Create a new event instance.
     */
    public function __construct(Stay $stay, $data)
    {
        $this->data = $data;
        $this->stay = $stay;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('stay-sessions.' . $this->stay->id);
    }

    public function broadcastWith()
    {
        return ['data' => $this->data];
    }
}
