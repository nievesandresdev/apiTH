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
    protected $withAvailability;
    protected $userToNotify;
    protected $chatService;
    protected $mailService;
    protected $url;
    protected $time;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($nameJob, $guestId, $stay, $userToNotify, $withAvailability = false,$url = null,$time = null)
    {
        $this->nameJob = $nameJob;
        $this->guestId = $guestId;
        $this->stay = $stay;
        $this->userToNotify = $userToNotify;
        $this->withAvailability = $withAvailability;
        $this->url = $url;
        $this->time = $time;
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

        //Log::info("hotel_id / NofityPendingChat: ".$this->stay->hotel_id);

        if($this->withAvailability){
            $isHosterAvailable = $this->chatService->getAvailavilityByHotel($this->stay->hotel_id);
            if(!$isHosterAvailable){
                Log::info("Hoster no disponible, no se envia la notificacion");
                return; // Detiene la ejecución del job de forma controlada.
            }
        }

        Log::info("NofityPendingChat");
        $chat = Chat::where('stay_id',$this->stay->id)->where('guest_id',$this->guestId)->first();
        if($chat->pending){
            // sendEventPusher('private-notify-unread-msg-hotel.' . $this->stay->hotel_id, 'App\Events\NotifyUnreadMsg',
            //     [
            //         'showLoadPage' => false,
            //         'guest_id' => $this->guestId,
            //         'stay_id' => $this->stay->id,
            //         'room' => $this->stay->room,
            //         'guest' => true,
            //         'text' => 'Tienes un chat sin responder',
            //         'automatic' => false,
            //         'add' => false,'pending' => false,//es falso en el input pero true en la bd
            //     ]
            // );

            $unansweredMessagesData = $this->chatService->unansweredMessagesData($chat->id,'ToHoster');
            Log::info("NofityPendingChat". json_encode($unansweredMessagesData));
            Log::info("NofityPendingChat". json_encode($this->userToNotify));
            Log::info("NofityPendingChattime". json_encode($this->time));

            foreach ($this->userToNotify as $user) {
                $this->mailService->sendEmail(new ChatEmail($unansweredMessagesData,$this->url,$this->time,$user['id'],'pending'), $user['email']);
                //$this->mailService->sendEmail(new ChatEmail($unansweredMessagesData,$this->url,null,2337, 'test'), 'francisco20990@gmail.com');
                //$this->mailService->sendEmail(new ChatEmail($unansweredLastMessageData,$urlChat,$this->time,$user->id, 'new'), $email);
            }
        }
    }
}
