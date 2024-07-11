<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Http\Resources\GuestResource;
use App\Jobs\Chat\{AutomaticMsg, SendPendingMessageEmail,NotifyUnreadMsg};
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\Stay;
use App\Http\Resources\StayResource;
use App\Models\Chat;
use App\Models\ChatSetting;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Services\Hoster\Users\{UserServices};
use App\Models\hotel;
use Illuminate\Support\Facades\Mail;
use App\Mail\Chats\ChatEmail;
use App\Services\MailService;



class ChatService {

    public $settings;
    public $userServices;
    public $mailService;

    function __construct(
        ChatSettingsServices $_ChatSettingsServices,
        UserServices $userServices,
        MailService $_MailService
    )
    {
        $this->settings = $_ChatSettingsServices;
        $this->userServices = $userServices;
        $this->mailService = $_MailService;
    }

    public function sendMsgToHoster ($request) {
        try{
            $hotel = $request->attributes->get('hotel');
            $settingsPermissions = $this->settings->getAll($hotel->id);
            /**
             * trae los ususarios y sus roles asociados al hotel en cuestion
             */
                $queryUsers = $this->userServices->getUsersHotelBasicData($hotel->id);

                // Extraer los roles de email_notify_new_feedback_to
                $rolesToNotify = collect($settingsPermissions['email_notify_new_feedback_to']);

                // Filtrar los usuarios que tengan uno de esos roles
                $filteredUsers = $queryUsers->filter(function ($user) use ($rolesToNotify) {
                    return $rolesToNotify->contains($user['role']);
                });

            /** fin traer user asociados y permisos */
            DB::beginTransaction();
            $langPage = $request->langWeb;

            $guestId = $request->guestId;
            $stayId = $request->stayId;

            $guest = new GuestResource(Guest::find($guestId));
            $stay = new StayResource(Stay::find($stayId));



            $chat = $guest->chats()
                ->updateOrCreate([
                    'stay_id' => $stayId
                ], [
                    'pending' => true,
            ]);


            $chatMessage = new ChatMessage([
                'chat_id' => $chat->id,
                'text' => $request->text,
                'status' => 'Entregado',
                'by' => 'Guest',
                'automatic' => false
            ]);
            //Mail::to('francisco20990@gmail.com')->send(new ChatEmail('new'));
            /* $this->mailService->sendEmail(new ChatEmail('sss'), "francisco20990@gmail.com");
            return [
                'response' => 'successReturn',
                'guest' => $guest,
                'stay' => $stay,
                'chat' => $chat,
                'chatMessage' => $chatMessage,
                'filteredUsers' => $filteredUsers,
                'settingsPermissions' => $settingsPermissions,
                'hotel' => $hotel
            ]; */

            //Mail::to($filteredUsers->pluck('email'))->send(new ChatEmail('pending', $hotel));


            $msg = $guest->chatMessages()->save($chatMessage);
            $msg->load('messageable');
            if($msg){
                $hotel = $request->attributes->get('hotel');
                $defaultChatSettingsArray  = defaultChatSettings();
                $settings = ChatSetting::where('hotel_id',$hotel->id)->first() ?? $defaultChatSettingsArray;
                sendEventPusher('private-update-chat.' . $stay->id, 'App\Events\UpdateChatEvent', ['message' => $msg]);
                sendEventPusher('private-noti-hotel.' . $hotel->id, 'App\Events\NotifyStayHotelEvent',
                    [
                        'stay_id' => $stay->id,
                        'chat_id' => $chat->id,
                        'hotel_id' => $hotel->id,
                        'room' => $stay->room,
                        'guest' => true,
                        'text' => $msg->text,
                        'automatic' => false,
                        'add' => true,'pending' => false,//es falso en el input pero true en la bd
                    ]
                );

                // Antes de encolar nuevos trabajos, elimina los trabajos antiguos.
                DB::table('jobs')->where('payload', 'like', '%send-by' . $guest->id . '%')->delete();

                //se envia la notificacion si el hoster no responde en 2 min
                NotifyUnreadMsg::dispatch('send-by'.$guest->id,$msg->id,$stay->hotel_id,$stay->id,$stay->room)->delay(now()->addMinutes(2));

                //se envia el mensaje si el hoster no responde en 1 min
                if($request->isAvailable && $settings->first_available_show){
                    AutomaticMsg::dispatch('send-by'.$guest->id,$stay->hotel_id,$stay->id,$msg->id,$chat->id,$settings->first_available_msg[$langPage])->delay(now()->addMinutes(1));
                }
                //se envia el mensaje si el hoster no responde en 5 min
                if($request->isAvailable && $settings->second_available_show){
                    AutomaticMsg::dispatch('send-by'.$guest->id,$stay->hotel_id,$stay->id,$msg->id,$chat->id,$settings->second_available_msg[$langPage])->delay(now()->addMinutes(5));//5
                }
                //se envia el mensaje si el hoster no responde en 10 min
                if($request->isAvailable && $settings->three_available_show){
                    AutomaticMsg::dispatch('send-by'.$guest->id,$stay->hotel_id,$stay->id,$msg->id,$chat->id,$settings->three_available_msg[$langPage])->delay(now()->addMinutes(10));//10

                    /** enviar Mail */
                        $mailData = [
                            'guest' => $guest,
                            'stay' => $stay,
                            'msg' => $msg,
                            'messageContent' => $settings->three_available_msg[$langPage]
                        ];
                        // evento
                        SendPendingMessageEmail::dispatch($mailData)->delay(now()->addMinutes(10));
                    /** fin enviar mail */
                }

                //se envia el mensaje si no hay agente disponible
                if(!$request->isAvailable && $settings->not_available_show){
                    $chatMessage = new ChatMessage([
                        'chat_id' => $chat->id,
                        'text' => $settings->not_available_msg[$langPage],
                        'status' => 'Entregado',
                        'by' => 'Hoster',
                        'automatic' => true
                    ]);

                    $msg = $guest->chatMessages()->save($chatMessage);
                    $msg->load('messageable');
                    sendEventPusher('private-update-chat.' . $stay->id, 'App\Events\UpdateChatEvent', ['message' => $msg]);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function loadMessages ($request) {
        try{
        $chat = $this->findByGuestStay($request->guestId, $request->stayId);
         if($chat){
            return $chat->messages()->get();
         }
         return [];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function markMsgsAsRead ($request) {
        try{
            $chat = $this->findByGuestStay($request->guestId, $request->stayId);
            if($chat){
                $chat->messages()->where([
                    ['by', '=', $request->rol],
                    ['status', '=', 'Entregado']
                ])->update(['status' => 'Leído']);
                sendEventPusher('private-update-chat.' . $request->stayId, 'App\Events\MsgReadChatEvent', 'Actualizado');
            }
         return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function unreadMsgs ($request) {
        try{
            $chat = $this->findByGuestStay($request->guestId, $request->stayId);
            if($chat){
                $countUnreadMsgs = $chat->messages()->where([['by', '=', $request->rol],['status', '=', 'Entregado']])->count();
                return $countUnreadMsgs;
            }
         return 0;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findByGuestStay($guestId, $stayId){
        return Chat::where('guest_id', $guestId)
                        ->where('stay_id', $stayId)
                        ->first();
    }


}
