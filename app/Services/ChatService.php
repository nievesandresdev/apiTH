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
use App\Jobs\Queries\NotifyPendingQuery;
use App\Models\Chat;
use App\Models\ChatSetting;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Services\Hoster\Users\{UserServices};
use App\Models\hotel;
use Illuminate\Support\Facades\Mail;
use App\Mail\Chats\ChatEmail;
use App\Services\MailService;
use App\Jobs\Chat\NofityPendingChat;
use App\Models\ChatHour;

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
            /**
             * enviar mensaje
             */
            $hotel = $request->attributes->get('hotel');
            $settings = $this->settings->getAll($hotel->id);

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
            /**
             * actualizacion de notificaciones en el saas
             *
             */

            $msg = $guest->chatMessages()->save($chatMessage);
            $msg->load('messageable');
            if($msg){
                sendEventPusher('private-update-chat.' . $stay->id, 'App\Events\UpdateChatEvent', ['message' => $msg]);
                sendEventPusher('private-noti-hotel.' . $hotel->id, 'App\Events\NotifyStayHotelEvent',
                    [
                        'showLoadPage' => false,
                        'pendingCountChats' => 1,
                        'stayId' => $stay->id,
                        'add' => true,'pending' => false
                    ]
                );
                sendEventPusher('private-update-stay-list-hotel.' . $hotel->id, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
                //notificacion push para el navegador del hoster(nuevo mensaje)
                sendEventPusher('private-notify-unread-msg-hotel.' . $hotel->id, 'App\Events\NotifyUnreadMsg',
                [
                    'showLoadPage' => false,
                    'stay_id' => $stay->id,
                    'guest_id' => $guest->id,
                    'chat_id' => $chat->id,
                    'hotel_id' => $hotel->id,
                    'room' => $stay->room,
                    'guest' => true,
                    'text' => $msg->text,
                    'automatic' => false,
                    'add' => true,'pending' => false,//es falso en el input pero true en la bd
                ]
            );
                /**
                 * cola de mensajes automaticos para el huesped
                 *
                */
                // Antes de encolar nuevos trabajos, elimina los trabajos antiguos guardados para el mismo huesped.
                DB::table('jobs')->where('payload', 'like', '%send-by' . $guest->id . '%')->delete();

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

            /**
             * notificaciones push y por email para el hoster
             *
             */
            $this->notificationsToHosterWhenSendMsg($chat, $hotel, $settings, $stay, $guest, $msg);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function notificationsToHosterWhenSendMsg($chat, $hotel, $settings, $stay, $guest, $msg){

        try{
            /**
             * trae los ususarios y sus roles asociados al hotel en cuestion
             */
            $queryUsers = $this->userServices->getUsersHotelBasicData($hotel->id);

            // Extraer los roles de usuario a notificar para un nuevo mensaje
            $rolesToNotifyNewMsg = collect($settings['email_notify_new_message_to']);
            $getUsersRoleNewMsg = $queryUsers->filter(function ($user) use ($rolesToNotifyNewMsg) {
                return $rolesToNotifyNewMsg->contains($user['role']);
            });
            // Extraer los roles de usuario a notificar para chat pendiente luego de 10 min
            $rolesToNotifyPending10Min = collect($settings['email_notify_pending_chat_to']);
            $getUsersRolePending10Min = $queryUsers->filter(function ($user) use ($rolesToNotifyPending10Min) {
                return $rolesToNotifyPending10Min->contains($user['role']);
            });
            // Extraer los roles de usuario a notificar para chat pendiente luego de 30 min
            $rolesToNotifyPending30Min = collect($settings['email_notify_not_answered_chat_to']);
            $getUsersRolePending30Min = $queryUsers->filter(function ($user) use ($rolesToNotifyPending30Min) {
                return $rolesToNotifyPending30Min->contains($user['role']);
            });

            /**
             * notificacion para cuando el hoster reciba un nuevo mensaje
             *
             */
            $unansweredMessagesData = $this->unansweredMessagesData($chat->id);
            if ($getUsersRoleNewMsg->isNotEmpty()) {
                $getUsersRoleNewMsg->each(function ($user) use ($unansweredMessagesData) {
                    //http://localhost:82/estancias/{stayId}/chat?g=guestId
                    $this->mailService->sendEmail(new ChatEmail($unansweredMessagesData,'new'), $user['email']); //email new message chat
                });
            }
            $guestId = $guest->id;
            /**
             * notificacion a enviarse a los 10min si el chat aun esta pendiente
             *
             */
            NofityPendingChat::dispatch('send-by'.$guestId, $guestId, $stay, $getUsersRolePending10Min)->delay(now()->addMinutes(10));
            /**
             * notificacion a enviarse a los 30min si el chat aun esta pendiente y hay personal disponible
             *
             */
            $withAvailability = true;
            NofityPendingChat::dispatch('send-by'.$guestId, $guestId, $stay, $getUsersRolePending10Min, $withAvailability)->delay(now()->addMinutes(30));
            //aqui va
        } catch (\Exception $e) {
            return $e;
        }

    }

    public function unansweredMessagesData($chatId){

        try{
            $url = config('app.hoster_url');
            $unansweredMessages = ChatMessage::whereHas('chat', function ($query) use ($chatId) { //los mensajes del ultimo chat pendiung 1
                $query->where('id', $chatId)
                    ->where('pending', 1);
            })
            ->where('automatic', 0)
            ->where('by', '!=', 'Hoster')
            ->with(['chat','messageable'])
            ->latest()
            ->get();

            $unansweredMessagesData = $unansweredMessages->map(function ($message) use ($url) { //map
                $guestName = null;
                if ($message->messageable_type === 'App\Models\Guest') {
                    $guestName = $message->messageable->name;
                }

                return [
                    'guest_name' => $guestName,
                    'message_text' => $message->text,
                    'sent_at' => $message->created_at->format('d M - H:i'),
                    'url' => $url.'estancias/'.$message->chat->stay_id.'/chat?g='.$message->chat->guest_id,
                ];
            });

            return $unansweredMessagesData;
        } catch (\Exception $e) {
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

    public function getAvailavilityByHotel($hotelId) {
        $now = Carbon::now();
        $dayOfWeekEnglish = $now->format('l'); // Día de la semana en inglés

        // Mapeo de días de la semana del inglés al español
        $daysMap = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo'
        ];

        // Convertir el día de la semana a español
        $dayOfWeek = $daysMap[$dayOfWeekEnglish] ?? null;

        if (!$dayOfWeek) {
            // Si no se encuentra el día, retorna false (esto no debería suceder normalmente)
            return false;
        }

        try {
            // Busca los horarios disponibles para el hotel y el día de la semana actual en español
            $availability = ChatHour::where('hotel_id', $hotelId)
                                    ->where('day', $dayOfWeek)
                                    ->where('active', true)
                                    ->get();

            foreach ($availability as $day) {
                foreach ($day->horary as $horary) {
                    $startTime = Carbon::createFromTimeString($horary['start']);
                    $endTime = Carbon::createFromTimeString($horary['end']);
                    // Verifica si la hora actual está dentro de alguno de los rangos horarios
                    if ($now->between($startTime, $endTime)) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            // Considera manejar la excepción de manera más específica o registrarla
            return false;
        }
    }

}
