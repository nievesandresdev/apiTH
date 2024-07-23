<?php

namespace App\Services\Hoster\Chat;

use App\Jobs\Chat\UnReadGuest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Stay;
use Illuminate\Support\Facades\DB;

class ChatHosterServices {


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

    public function sendMsg($guestId, $stayId, $text, $hotelId){
        try {
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
                'by' => 'Hoster'
            ]);

            $msg = $chat->messages()->save($chatMessage);

            //si existe algun job para notificacion no se acumularan jobs duplicados
            $exists_job = DB::table('jobs')->where('payload', 'like', '%by-hoster' . $chat->id . '%')->count();
            if(!$exists_job){
                UnReadGuest::dispatch('by-hoster'.$chat->id, $stayId, $chatMessage->id)->delay(now()->addMinutes(10));
            }
            DB::commit();
            //send message
            sendEventPusher('private-update-chat.' . $stayId, 'App\Events\UpdateChatEvent', ['message' => $msg]);
            sendEventPusher('private-noti-hotel.' . $hotelId, 'App\Events\NotifyStayHotelEvent',
                [
                    'stay_id' => $stayId,
                    'chat_id' => $chat->id,
                    'hotel_id' => $hotelId,
                    'automatic' => '0',
                    'add' => false,'pending' => true,  //es true en el input pero false en la bd
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function togglePending($guestId, $stayId, $pendingBool){
        try {
            $stay = Stay::select('id','hotel_id')->first($stayId);
            $stay->touch();    

            $chat = Chat::updateOrCreate([
                'stay_id' => $stayId,
                'guest_id' => $guestId
            ], [
                'pending' => $pendingBool,
            ]);

            sendEventPusher('private-noti-hotel.' . $stay->hotel_id, 'App\Events\NotifyStayHotelEvent',
                [
                    'stay_id' => $stay->id,
                    'chat_id' => $chat->id,
                    'automatic' => '0',
                    'hotel_id' => $stay->hotel_id,
                    'add' => boolval($pendingBool),
                    'pending' => !boolval($pendingBool)
                ]
            );
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
            }])->get();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
}
