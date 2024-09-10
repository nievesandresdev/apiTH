<?php

namespace App\Services\Hoster\Chat;

use App\Jobs\Chat\UnReadGuest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Stay;
use App\Services\Hoster\Queries\QueryHosterServices;
use App\Services\Hoster\Stay\StaySessionServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatHosterServices {

    public $queryHosterServices;
    public $staySessionServices;

    function __construct(
        QueryHosterServices $_QueryHosterServices,
        StaySessionServices $_StaySessionServices
    )
    {
        $this->queryHosterServices = $_QueryHosterServices;
        $this->staySessionServices = $_StaySessionServices;
    }

    public function pendingCountByHotel($hotel){
        try {
            return $hotel->stays()
            ->whereHas('chats', function ($query) {
                $query->where('pending', 1);
            })
            ->count();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function pendingCountByStay($stayId){
        try {
            return Chat::where('stay_id',$stayId)
                ->where('pending', 1)   
                ->count();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    public function getDataRoom($stayId, $guestId){
        try {
            $chat = Chat::where('stay_id',$stayId)
                ->where('guest_id',$guestId)
                ->first();
            // $msg = new ChatMessage(['status' => 'Leído']);
            $messages = [];
            if($chat){
                $chat->messages()
                    ->where('by','Guest')
                    ->where('status','Entregado')
                    ->update(['status' => 'Leído']);
                $messages = $chat->messages()->get();
                $messages->load('messageable');
            }       
            return [
                'chat' => $chat,
                'messages' => $messages
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function sendMsg($guestId, $stayId, $text, $hotel, $data) {
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            DB::beginTransaction();

            $chat = Chat::updateOrCreate([
                'stay_id' => $stayId,
                'guest_id' => $guestId
            ], [
                'pending' => false,
            ]);
            
            //save message
            $chatMessage = new ChatMessage([
                'chat_id' => $chat->id,
                'text' => $text,
                'status' => 'Entregado',
                'by' => 'Hoster',
                'messageable_id' => $hotel->id,
                'messageable_type' => 'App\Models\hotel'
            ]);

            $msg = $chat->messages()->save($chatMessage);
            DB::commit();
            //si existe algun job para notificacion no se acumularan jobs duplicados
            DB::table('jobs')->where('payload', 'like', '%by-hoster' . $chat->id . '%')->delete();
            UnReadGuest::dispatch('by-hoster'.$chat->id, $hotel, $chat->id, $guestId)->delay(now()->addMinutes(10));
            $hotelId = $hotel->id;
            $count = $this->pendingCountByStay($stayId);
            //send message
            sendEventPusher('private-update-chat.' . $stayId, 'App\Events\UpdateChatEvent', [
                'message' => $msg,
                'chatData' => $chat,
            ]);
            sendEventPusher('private-noti-hotel.' . $hotelId, 'App\Events\NotifyStayHotelEvent',
                [
                    'showLoadPage' => false,
                    'stayId' => $stayId,
                    'chat_id' => $chat->id,
                    'hotel_id' => $hotelId,
                    'pendingCountChats' => $count,
                    'automatic' => '0',
                    'add' => false,'pending' => true,  //es true en el input pero false en la bd
                ]
            );
            sendEventPusher('private-update-stay-list-hotel.' . $hotelId, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function togglePending($guestId, $stayId, $pendingBool, $hotelId, $data) {
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            // $stay = Stay::select('id','hotel_id')->first($stayId);
            // $stay->touch();    
            Chat::updateOrCreate([
                'stay_id' => $stayId,
                'guest_id' => $guestId
            ], [
                'pending' => $pendingBool,
            ]);
            
            $count = $this->pendingCountByStay($stayId);
            // sendEventPusher('private-noti-hotel.' . $stay->hotel_id, 'App\Events\NotifyStayHotelEvent',
            //     [
            //         'showLoadPage' => false,
            //         'stay_id' => $stay->id,
            //         'chat_id' => $chat->id,
            //         'automatic' => '0',
            //         'hotel_id' => $stay->hotel_id,
            //         'add' => boolval($pendingBool),
            //         'pending' => !boolval($pendingBool)
            //     ]
            // );
            sendEventPusher('private-noti-hotel.' . $hotelId, 'App\Events\NotifyStayHotelEvent',
                [
                    'showLoadPage' => false,
                    'pendingCountChats' => $count,
                    'stayId' => $stayId,
                    'pending' => !boolval($pendingBool),
                    'hotelId' => $hotelId,
                ]
            );
            sendEventPusher('private-update-stay-list-hotel.' . $hotelId, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
            return ['chatStatus'=>boolval($pendingBool)];
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    public function getGuestListWNoti($stayId){
        try {
            $stay = Stay::select('id')->where('id',$stayId)->first();
            return $stay->guests()->with(['chats' => function($query) use ($stayId) {
                $query->select('stay_id','id','guest_id')
                ->where('stay_id', $stayId)->withCount(['messages' => function($query) {
                    $query->where('status', 'Entregado')->where('by','Guest');
                }]);
            }])->select('guests.name','guests.id')->get();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function markGuesMsgstAsRead($stayId, $guestId){
        try {
            $chat = Chat::where('stay_id',$stayId)->where('guest_id',$guestId)->first();
            $chat->messages()->where('by','Guest')->where('status','Entregado')->update(['status' => 'Leído']);
            return $this->getGuestListWNoti($stayId);
        } catch (\Exception $e) {
            return $e;
        }
    }
    
}
