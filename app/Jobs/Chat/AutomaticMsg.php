<?php
namespace App\Jobs\Chat;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Models\hotel;
use App\Models\User;
use App\Services\Hoster\Chat\ChatHosterServices;

class AutomaticMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $guestId;
    protected $msg_id;
    protected $chat_id;
    protected $stay_id;
    protected $hotel_id;
    protected $text;
    protected $chatHosterServices;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($guestId,$hotel_id,$stay_id,$msg_id,$chat_id,$text)
    {
        $this->guestId = $guestId;
        $this->msg_id = $msg_id;
        $this->chat_id = $chat_id;
        $this->stay_id = $stay_id;
        $this->hotel_id = $hotel_id;
        $this->text = $text;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChatHosterServices $_ChatHosterServices)
    {
        Log::info('Automatic MSG $chat_id '.json_encode($this->chat_id));
        $this->chatHosterServices = $_ChatHosterServices;
        $message = ChatMessage::with('chat')->find($this->msg_id);

        $explode = explode("send-by", $this->guestId);
        $guestId = $explode[1];
        
        if($message->chat->pending){
            $chatMessage = new ChatMessage([
                'chat_id' => $this->chat_id,
                'text' => $this->text,
                'status' => 'Entregado',
                'by' => 'Hoster',
                'automatic' => true
            ]);
            
            $hotel = hotel::find($this->hotel_id);
            $msg = $hotel->chatMessages()->save($chatMessage);
            $msg->load('messageable');
            
            $countUnreadMsgsByGuest = $this->chatHosterServices->countUnreadMsgsByGuest($this->chat_id);
            sendEventPusher('private-update-chat.' . $guestId, 'App\Events\UpdateChatEvent', [
                'message' => $msg,
                'chatData' => $message->chat,
                'countUnreadMsgsByGuest' => $countUnreadMsgsByGuest
            ]);
            // sendEventPusher('private-update-chat.' . $guestId, 'App\Events\UpdateChatEvent', ['message' => $msg]);
            sendEventPusher('private-notify-unread-msg-guest.' . $guestId, 'App\Events\NotifyUnreadMsgGuest', [
                'countUnreadMsgsByGuest' => $countUnreadMsgsByGuest
            ]);
        }
    }
}
