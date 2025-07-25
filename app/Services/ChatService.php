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
use Illuminate\Support\Facades\App;
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

            $notificationFilters = [
                'newChat' => true,
            ];

            $specificChannels = ['push','email'];

            $usersByChannel = $this->userServices->getUsersHotelBasicData($hotel->id, $notificationFilters, $specificChannels);

            // Usuarios con notificaciones push
            $pushUsers = $usersByChannel['push'];
            $emailUserChatNew = $usersByChannel['email'];
            //Log::info('emailUserChatNew'. $emailUserChatNew);


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
            // Log::info('sendMsgToHoster $chatMessage'. json_encode($chatMessage));

            $msg = $guest->chatMessages()->save($chatMessage);

            //$this->notificationsToHosterWhenSendMsg($chat, $hotel, $settings, $stay, $guest, $msg);
            $msg->load('messageable');
            if($msg){
                sendEventPusher('private-update-chat.' . $guestId, 'App\Events\UpdateChatEvent', [
                    'message' => $msg,
                    'chatData' => $chat,
                ]);
                sendEventPusher('private-noti-hotel.' . $hotel->id, 'App\Events\NotifyStayHotelEvent',
                    [
                        'showLoadPage' => false,
                        'pendingCountChats' => 1,
                        'stayId' => $stay->id,
                        'add' => true,'pending' => false
                    ]
                );
                sendEventPusher('private-update-stay-list-hotel.' . $hotel->id, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);

                if ($pushUsers->isNotEmpty()) {
                    $pushUsers->each(function ($user) use ($chat, $stay, $guest, $msg, $hotel) {
                        //Log::info('sendMsgToHoster $user'. json_encode($user));
                        sendEventPusher('private-notify-unread-msg-hotel.' . $hotel->id, 'App\Events\NotifyUnreadMsg', [
                            'showLoadPage' => false,
                            'stay_id' => $stay->id,
                            'guest_id' => $guest->id,
                            'chat_id' => $chat->id,
                            'hotel_id' => $hotel->id,
                            'room' => $stay->room,
                            'user_id' => $user->id,
                            'guest' => true,
                            'text' => $msg->text,
                            'automatic' => false,
                            'concept' => "new",
                            'add' => true,
                            'pending' => false,
                        ]);
                    });
                }

                /**
                 * cola de mensajes automaticos para el huesped
                 *
                */
                // Antes de encolar nuevos trabajos, elimina los trabajos antiguos guardados para el mismo huesped.
                DB::table('jobs')->where('payload', 'like', '%send-by' . $guest->id . '%')->delete();
                //se envia el mensaje si el hoster no responde en 1 min
                if($request->isAvailable && $settings->first_available_show){
                    Log::info('Automatic MSG 1 $chat_id '.json_encode($chat->id));
                    AutomaticMsg::dispatch('send-by'.$guest->id,$stay->hotel_id,$stay->id,$msg->id,$chat->id,$settings->first_available_msg[$langPage])->delay(now()->addMinutes(1));
                }
                //se envia el mensaje si el hoster no responde en 5 min
                if($request->isAvailable && $settings->second_available_show){
                    Log::info('Automatic MSG 2 $chat_id '.json_encode($chat->id));
                    AutomaticMsg::dispatch('send-by'.$guest->id,$stay->hotel_id,$stay->id,$msg->id,$chat->id,$settings->second_available_msg[$langPage])->delay(now()->addMinutes(5));//5
                }
                //se envia el mensaje si el hoster no responde en 10 min
                if($request->isAvailable && $settings->three_available_show){
                    Log::info('Automatic MSG 3 $chat_id '.json_encode($chat->id));
                    AutomaticMsg::dispatch('send-by'.$guest->id,$stay->hotel_id,$stay->id,$msg->id,$chat->id,$settings->three_available_msg[$langPage])->delay(now()->addMinutes(10));//10

                }
                //se envia el mensaje si no hay agente disponible
                Log::info('isAvailable '.json_encode($request->isAvailable));
                Log::info('settings->not_available_show '.json_encode($settings->not_available_show));
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
                    sendEventPusher('private-update-chat.' . $guestId, 'App\Events\UpdateChatEvent', [
                        'message' => $msg,
                        'chatData' => $chat,
                    ]);
                }
                /**
             * notificaciones push y por email para el hoster
             *
             */
            /* $notificationFiltersNewChat = [
                'newChat' => true,
            ];


            $queryUsersNewchat = $this->userServices->getUsersHotelBasicData($hotel->id, $notificationFiltersNewChat);
            $unansweredLastMessageData = $this->unansweredMessagesData($chat->id,'ToHoster',true);
            $urlChat = config('app.hoster_url').'estancias/'.$stay->id.'/chat?g='.$guest->id;

            Log::info('queryUsersNewchat'. $queryUsersNewchat);

             // Verificar si hay usuarios
             if ($queryUsersNewchat->isNotEmpty()) {
                Log::info('queryUsersNewchatSIPI'. $queryUsersNewchat);
                // Enviar correo usuarios con newchat true
                $queryUsersNewchat->each(function ($user) use ($unansweredLastMessageData, $urlChat) {
                    $email = $user->email;
                    Log::info('queryUsersNewchatSIPIEACH '. $email);
                    //$this->mailService->sendEmail(new ChatEmail($unansweredLastMessageData,$urlChat, 'new'), francisco);
                    $this->mailService->sendEmail(new ChatEmail($unansweredLastMessageData,$urlChat, 'test'), 'xxxx@gmail.com');
                });
            } */

             $this->notificationsToHosterWhenSendMsg($chat, $hotel, $settings, $stay, $guest, $msg,$emailUserChatNew);
            }



            DB::commit();
            return true;
        } catch (\Exception $e) {
            Log::error('Error service sendMsgToHoster: ' . $e->getMessage());
            DB::rollback();
            return $e;
        }
    }

    public function notificationsToHosterWhenSendMsg($chat, $hotel, $settings, $stay, $guest, $msg,$emailUserChatNew = null){

        try{
            $urlChat = config('app.hoster_url').'estancias/'.$stay->id.'/chat?g='.$guest->id;

            /**
             * mensajes (TODOS) sin leer
             */
           /*  $unansweredMessagesData = $this->unansweredMessagesData($chat->id); */




            /**
             * ultimo mensaje sin leer (nuevo mensaje)
             */
            $unansweredLastMessageData = $this->unansweredMessagesData($chat->id,'ToHoster',true);
            //Log::info('queryUsersNewchat'. $stay->hotel_id);
            /**
             * trae los ususarios y sus roles asociados al hotel en cuestion
             */
           /*  $notificationFiltersNewChat = [
                'newChat' => true,
            ];

            $notificacionFilterChatPending10 = [
                'PendingChat10' => true,
            ];

            $notificacionFilterChatPending30 = [
                'pendingChat30' => true,
            ];

            $queryUsersNewchat = $this->userServices->getUsersHotelBasicData($hotel->id, $notificationFiltersNewChat,true); */



            // Verificar si hay usuarios
            if ($emailUserChatNew) {
                $communication = $stay->hotel->hotelCommunications->firstWhere('type', 'email');
                $shouldSend = !$communication || $communication->new_chat_email;



                if($shouldSend){ //validacion de trigger de email
                        // Enviar correo usuarios con newchat true
                        $emailUserChatNew->each(function ($user) use ($unansweredLastMessageData, $urlChat,$stay,$guest) {
                            $email = $user->email;
                            App::setLocale('es');
                        $data = [
                            'urlPrivacy' => buildUrlWebApp($stay->hotel->subdomain, $stay->hotel->subdomain,'privacidad',"e={$stay->id}&g={$guest->id}&email=true&lang=es"),
                            'urlFooterEmail' => buildUrlWebApp($stay->hotel->subdomain, $stay->hotel->subdomain,'no-notificacion',"e={$stay->id}&g={$guest->id}")
                        ];

                        $this->mailService->sendEmail(new ChatEmail($unansweredLastMessageData,$urlChat,null,$user->id, 'new',$stay->hotel,$data), $email);
                        Log::info('newChatService'. $data);
                    });
                }
            }

            // Extraer los roles de usuario a notificar para un nuevo mensaje
            /* $rolesToNotifyNewMsg = collect($settings->email_notify_new_message_to);
            $getUsersRoleNewMsg = $queryUsers->filter(function ($user) use ($rolesToNotifyNewMsg) {
                return $rolesToNotifyNewMsg->contains($user['role']);
            });
            // Extraer los roles de usuario a notificar para chat pendiente luego de 10 min
            $rolesToNotifyPending10Min = collect($settings->email_notify_pending_chat_to);
            $getUsersRolePending10Min = $queryUsers->filter(function ($user) use ($rolesToNotifyPending10Min) {
                return $rolesToNotifyPending10Min->contains($user['role']);
            });
            // Extraer los roles de usuario a notificar para chat pendiente luego de 30 min
            $rolesToNotifyPending30Min = collect($settings->email_notify_not_answered_chat_to);
            $getUsersRolePending30Min = $queryUsers->filter(function ($user) use ($rolesToNotifyPending30Min) {
                return $rolesToNotifyPending30Min->contains($user['role']);
            }); */

            /**
             * notificacion para cuando el hoster reciba un nuevo mensaje
             *
             */


            /* if ($queryUsers->isNotEmpty()) {
                $getUsersRoleNewMsg->each(function ($user) use ($unansweredMessagesData) {
                    //http://localhost:82/estancias/{stayId}/chat?g=guestId
                    $this->mailService->sendEmail(new ChatEmail($unansweredMessagesData,'new'), $user['email']); //email new message chat
                });
            } */

            $guestId = $guest->id;
            //querys de ususuarios segun el tiempo configurado en notifications
            /* $getUsersPending10 = $this->userServices->getUsersHotelBasicData($hotel->id, $notificacionFilterChatPending10);
            $getUsersRolePending30 = $this->userServices->getUsersHotelBasicData($hotel->id, $notificacionFilterChatPending30); */
            /**
             * notificacion a enviarse a los 10min si el chat aun esta pendiente
             * withAvailability =  false
             */
            //NofityPendingChat::dispatch('send-by'.$guestId, $guestId, $stay, $getUsersPending10,false,$urlChat,10)->delay(now()->addMinutes(10));
            /**
             * notificacion a enviarse a los 30min si el chat aun esta pendiente y hay personal disponible
             *
             */
            $withAvailability = true;
            //NofityPendingChat::dispatch('send-by'.$guestId, $guestId, $stay, $getUsersRolePending30, $withAvailability,$urlChat,30)->delay(now()->addMinutes(30));
        } catch (\Exception $e) {
            Log::error('Error al enviar notificaciones a hoster ChatService/notificationsToHosterWhenSendMsg: ' . $e->getMessage());
            return $e;
        }

    }

    /* public function unansweredMessagesData($chatId, $model = 'ToHoster'){

        try{

            $diff = 'Hoster';
            $pedding = 1;
            if($model == 'ToGuest'){
                $diff = 'Guest';
                $pedding = 0;
            }
            // Log::info('unansweredMessagesData '.json_encode([$chatId,$diff]));
            $url = config('app.hoster_url');
            $unansweredMessages = ChatMessage::whereHas('chat', function ($query) use ($chatId,$pedding) { //los mensajes del ultimo chat pendiung 1
                $query->where('id', $chatId)
                    ->where('pending', $pedding);
            })
            ->where('status', 'Entregado')
            ->where('automatic', 0)
            ->where('by', '!=', $diff)
            ->with(['chat','messageable'])
            ->orderBy('id','asc')
            // ->latest()
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

    } */

    public function unansweredMessagesData($chatId, $model = 'ToHoster', $onlyLast = false) {
        try {
            // Inicialización basada en el parámetro $model
            $expectedSender = 'Hoster';
            $pending = 1;

            if($model == 'ToGuest'){
                $expectedSender = 'Guest';
                $pending = 0;
            }

            $url = config('app.hoster_url');

            // consulta base
            $query = ChatMessage::whereHas('chat', function ($query) use ($chatId, $pending) {
                    $query->where('id', $chatId)
                          ->where('pending', $pending);
                })
                ->where('status', 'Entregado')
                ->where('automatic', 0)
                ->where('by', '!=', $expectedSender)
                ->with(['chat','messageable']);



            if ($onlyLast) {
                // Obtener solo el último mensaje pendiente
                $message = $query->latest('id')->orderBy('id', 'desc')->first();

                if ($message) {
                    $guestName = null;
                    if ($message->messageable_type === 'App\Models\Guest') {
                        $guestName = $message->messageable->name;
                    }

                    $messageData = [
                        'guest_name'   => $guestName,
                        'message_text' => $message->text,
                        'sent_at'      => $message->created_at->format('d M - H:i'),
                        'url'          => $url.'estancias/'.$message->chat->stay_id.'/chat?g='.$message->chat->guest_id,
                    ];

                    return collect($messageData);
                } else {
                    return null; // No hay mensajes pendientes
                }
            } else {
                // Obtener todos los mensajes pendientes
                $unansweredMessages = $query->orderBy('id', 'asc')->get();

                $unansweredMessagesData = $unansweredMessages->map(function ($message) use ($url) {
                    $guestName = null;
                    if ($message->messageable_type === 'App\Models\Guest') {
                        $guestName = $message->messageable->name;
                    }

                    return [
                        'guest_name'   => $guestName,
                        'message_text' => $message->text,
                        'sent_at'      => $message->created_at->format('d M - H:i'),
                        'url'          => $url.'estancias/'.$message->chat->stay_id.'/chat?g='.$message->chat->guest_id,
                    ];
                });

                return $unansweredMessagesData;
            }
        } catch (\Exception $e) {
            // Registrar el error y devolver una respuesta amigable
            Log::error('Error al obtener mensajes sin respuesta: ' . $e->getMessage());
            return response()->json(['error' => 'No se pueden recuperar los mensajes en este momento.'], 500);
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
                sendEventPusher('private-update-chat.' . $request->guestId, 'App\Events\MsgReadChatEvent', 'Actualizado');
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


    public function getAvailableLanguages ($hotelId) {
        try{
            $settings = ChatSetting::select('chat_settings.id','chat_settings.hotel_id')
                ->with('languages')
                ->where('hotel_id',$hotelId)->first();
        if($settings){
            return $settings->languages;
        }
        $default = defaultChatSettings();
        return $default->languages;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getAllSettings ($hotelId) {
        try {
            $default = ChatSetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = defaultChatSettings();
            }else{
                $default->load('languages');
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getChatHoursByHotel ($hotelId) {
        try {
             // 1. ¿Hay al menos un registro para este hotel?
            $hasAny = ChatHour::where('hotel_id', $hotelId)->exists();

            if (! $hasAny) {
                // 2. Nada en la BD ⇒ devolvemos el horario por defecto.
                return defaultChatHours();
            }

            // 3. Sí hay registros ⇒ devolvemos sólo los activos.
            return ChatHour::where('hotel_id', $hotelId)
                        ->where('active', 1)
                        ->get();
        } catch (\Exception $e) {
            return $e;
        }
    }
}
