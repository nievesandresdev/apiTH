<?php
namespace App\Jobs\Chat;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUnreadMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $guestId;
    protected $msg_id;
    protected $hotel_id;
    protected $stay_id;
    protected $room;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($guestId,$msg_id,$hotel_id,$stay_id,$room)
    {
        $this->guestId = $guestId;
        $this->msg_id = $msg_id;
        $this->hotel_id = $hotel_id;
        $this->stay_id = $stay_id;
        $this->room = $room;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = ChatMessage::find($this->msg_id);
        if($message->status == 'Entregado'){
            sendEventPusher('private-noti-hotel.' . $this->hotel_id, 'App\Events\NotifyStayHotelEvent', 
                [
                    'stay_id' => $this->stay_id,
                    'room' => $this->room,
                    'guest' => true,
                    'text' => 'Tienes un chat sin responder',
                    'automatic' => false,
                    'add' => false,'pending' => false,//es falso en el input pero true en la bd
                ]
            );
        }
    }
}
