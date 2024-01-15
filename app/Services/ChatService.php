<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Http\Resources\GuestResource;
use App\Jobs\Chat\AutomaticMsg;
use App\Jobs\Chat\NotifyUnreadMsg;
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\Stay;
use App\Http\Resources\StayResource;
use App\Models\Chat;
use App\Models\ChatSetting;

class ChatService {

    function __construct()
    {

    }

    public function sendMsgToHoster ($request) {
        try{ 
            DB::beginTransaction();
            $langPage = $request->langWeb;
                
            $stay = new StayResource(Stay::find($request->stayId));
            $chat = $stay->chat()->updateOrCreate([], ['pending' => true]);
            
            $guest = new GuestResource(Guest::find($request->guestId));
            $chatMessage = new ChatMessage([
                'chat_id' => $chat->id,
                'text' => $request->text,
                'status' => 'Entregado',
                'by' => 'Guest',
                'automatic' => false
            ]);
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
        $chat = $this->findById($request->stayId);    
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
            $chat = $this->findById($request->stayId);    
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
            $chat = $this->findById($request->stayId);    
            if($chat){
                $countUnreadMsgs = $chat->messages()->where([['by', '=', $request->rol],['status', '=', 'Entregado']])->count();
                if($countUnreadMsgs > 0){
                    return true;
                }
            }
         return false;
        } catch (\Exception $e) {
            return $e;
        }   
    }

    public function findById($id){
        return Chat::where('chatable_id', $id)
                        ->where('chatable_type', 'App\Models\Stay')
                        ->first();
    }


}