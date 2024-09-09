<?php
namespace App\Jobs\Chat;

use App\Mail\Chats\UnreadHosterMsg;
use App\Mail\Guest\MsgStay;
use App\Models\ChatMessage;
use App\Models\Guest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Models\hotel;
use App\Models\Stay;
use App\Models\StayNotificationSetting;
use App\Services\ChatService;
use Illuminate\Support\Facades\Mail;

class UnReadGuest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $hotel;
    protected $by;
    protected $chatServices;
    protected $chatId;
    protected $guestId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($by, $hotel, $chatId, $guestId)
    {
        $this->by = $by;
        $this->chatId = $chatId;
        $this->hotel = $hotel;
        $this->guestId = $guestId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChatService $_ChatService)
    {
        Log::info('UnReadGuest ');
        $this->chatServices = $_ChatService;
        $unansweredMessagesDataToGuest =  $this->chatServices->unansweredMessagesData($this->chatId,'ToGuest');
        // Log::info('$unansweredMessagesDataToGuest '.json_encode($unansweredMessagesDataToGuest));

        $link = url('webapp?g='.$this->guestId);
        $webappLink =  includeSubdomainInUrlHuesped($link, $this->hotel);
        // Log::info('$link '.json_encode($webappLink));
        // Log::info('$this->hotel '.json_encode($this->hotel));
        $guest = Guest::find($this->guestId);
        // Log::info('$guest '. json_encode($guest));
        Mail::to($guest->email)->send(new UnreadHosterMsg($unansweredMessagesDataToGuest, $this->hotel, $webappLink));

        // $settings =  StayNotificationSetting::where('hotel_id',$this->hotel->id)->first();
        // if(!$settings){
        //     $settingsArray = settingsNotyStayDefault();
        //     $settings = (object)$settingsArray;
        // }
        // Log::info('$settings');
        // $bool_send = $settings->chat_guest['when_unread_message'];
        // Log::info($bool_send);
        //,'staysShipments.guest'

        
        // if($guest['email'] && $bool_send['via_email']){
        //     $hotel = hotel::find($stay->hotel->id);
        //     // Mail::to($guest['email'])->send(new MsgStay($msg,$hotel));
        // }
        // if($guest['phone'] && $bool_send['via_sms']){
        //     sendSMS($guest['phone'],$msg,$stay->hotel->sender_for_sending_sms);
        // }

        
    }
}
