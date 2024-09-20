<?php

namespace App\Events\Stay;

use App\Models\Hotel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateStayListEvent  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $hotel;
    /**
     * Create a new event instance.
     */
    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('update-stay-list-hotel.' . $this->hotel->id);
    }

    public function broadcastWith()
    {
        return 'Actualizado';
    }
}
