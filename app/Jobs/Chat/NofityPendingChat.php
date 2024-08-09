<?php
namespace App\Jobs\Chat;

use App\Mail\Chats\ChatEmail;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\ChatService;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NofityPendingChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $nameJob;
    public $guestId;
    protected $stay;
    protected $userToNotify;
    protected $chatService;
    protected $mailService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($nameJob, $guestId, $stay, $userToNotify)
    {
        $this->nameJob = $nameJob;
        $this->guestId = $guestId;
        $this->stay = $stay;
        $this->userToNotify = $userToNotify;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChatService $_ChatService, MailService $_MailService)
    {
        $this->chatService = $_ChatService;
        $this->mailService = $_MailService;

        Log::info("NofityPendingChat");
        $chat = Chat::where('stay_id',$this->stay->id)->where('guest_id',$this->guestId)->first();
        if($chat->pending){
            sendEventPusher('private-notify-unread-msg-hotel.' . $this->stay->hotel_id, 'App\Events\NotifyUnreadMsg', 
                [
                    'showLoadPage' => false,
                    'guest_id' => $this->guestId,
                    'stay_id' => $this->stay->id,
                    'room' => $this->stay->room,
                    'guest' => true,
                    'text' => 'Tienes un chat sin responder',
                    'automatic' => false,
                    'add' => false,'pending' => false,//es falso en el input pero true en la bd
                ]
            );
            $unansweredMessagesData = $this->chatService->unansweredMessagesData($chat->id);
            Log::info("NofityPendingChat". json_encode($unansweredMessagesData));
            foreach ($this->userToNotify as $user) {
                $this->mailService->sendEmail(new ChatEmail($unansweredMessagesData,'new'), $user['email']);
            }
        }
    }
}
