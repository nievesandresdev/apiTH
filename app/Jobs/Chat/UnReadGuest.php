<?php
namespace App\Jobs\Chat;

use App\Mail\Guest\MsgStay;
use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Models\hotel;
use App\Models\Stay;
use App\Models\StayNotificationSetting;
use Illuminate\Support\Facades\Mail;

class UnReadGuest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $msg_id;
    protected $stay_id;
    protected $by;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($by,$stay_id,$msg_id)
    {
        $this->by = $by;
        $this->msg_id = $msg_id;
        $this->stay_id = $stay_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = ChatMessage::with('chat')->find($this->msg_id);
        Log::info('UnReadGuest ');
        if($message->status == 'Entregado'){
            $stay = Stay::with('guests','hotel')->find($this->stay_id);

            $settings =  StayNotificationSetting::where('hotel_id',$stay->hotel->id)->first();
            if(!$settings){
                $settingsArray = settingsNotyStayDefault();
                $settings = (object)$settingsArray;
            }
            Log::info('$settings');
            $bool_send = $settings->chat_guest['when_unread_message'];
            Log::info($bool_send);
            //,'staysShipments.guest'

            $guests_to_send = [];
            $guest_ids = [];

            // Huespedes relacionados a la estancia
            foreach($stay->guests as $g){
                array_push($guests_to_send, [
                    'id' => $g->id,
                    'email' => $g->email,
                    'phone' => $g->phone,
                    'name' => $g->name,
                    'lang' => $g->lang_web,
                ]);
                $guest_ids[] = $g->id; // Guarda el ID
            }

            //enviar notificaciones
            foreach ($guests_to_send as $guest) {
                $data = [
                    'stay_id' => $stay->id,
                    'guest_id' => $guest['id'],
                    'stay_lang' => $guest['lang'],
                    'msg_text' => 'Hola [nombre], Tienes un chat sin leer, ingresa al link para acceder al chat: [URL] El equipo del [nombre_del_hotel].',
                    'guest_name' => $guest['name'],
                    'hotel_name' => $stay->hotel->name,
                    'hotel_id' => $stay->hotel->id,
                ];
                $msg = prepareMessage($data);

                if($guest['email'] && $bool_send['via_email']){
                    $hotel = hotel::find($stay->hotel->id);
                    Mail::to($guest['email'])->send(new MsgStay($msg,$hotel));
                }
                if($guest['phone'] && $bool_send['via_sms']){
                    sendSMS($guest['phone'],$msg,$stay->hotel->sender_for_sending_sms);
                }
            }
        }
    }
}
