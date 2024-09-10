<?php

namespace App\Events\Chat;

use App\Models\Guest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyUnreadMsgGuest  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $guest;
    /**
     * Create a new event instance.
     */
    public function __construct(Guest $guest)
    {
        $this->guest = $guest;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notify-unread-msg-guest.' . $this->guest->id);
    }

    public function broadcastWith()
    {
        return [
            'guest' => $this->guest,
        ];
    }
}
