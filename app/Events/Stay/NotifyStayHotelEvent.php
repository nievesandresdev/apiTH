<?php

namespace App\Events\Stay;

use App\Models\hotel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyStayHotelEvent  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $hotel;
    public $stay_id;
    /**
     * Create a new event instance.
     */
    public function __construct(hotel $hotel, $stay_id)
    {
        $this->stay_id = $stay_id;
        $this->hotel = $hotel;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('update-chat.' . $this->hotel->id);
    }

    public function broadcastWith()
    {
        return [
            'stay_id' => $this->stay_id,
        ];
    }
}
