<?php

namespace App\Http\Resources;

use App\Models\ChatHour;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HotelBasicDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $user = $this->user[0];
        $is_subscribed = $user->subscriptions()->where(['name' => $this->subscription_active, 'stripe_status' => 'active'])->exists();
        $pending_chat_count =  $this->stays()
        ->whereHas('chats', function ($query) {
            $query->where('pending', 1);
        })->count();

        $pending_query_count = DB::table('queries')
            ->join('stays', 'stays.id', '=', 'queries.stay_id')
            ->select('stays.id as StayId','stays.hotel_id', 'queries.id', 'queries.answered', 'queries.attended')
            ->where('answered', 1)->where('attended', 0)
            ->where('hotel_id', $this->id)->count();

        return [
            "id"=> $this->id,
            "user_id" => $user->id,
            "name"=> $this->name,
            "subdomain"=> $this->subdomain,
            "type"=> $this->type,
            "zone"=> $this->zone,
            "image"=> $this->image,
            "del" => $this->del,
            "subscribed"=> $this->subscription_active ? $is_subscribed : false,
            "with_notificartion" => $pending_chat_count + $pending_query_count,
            "code"=>$this->code
        ];
        
    }
}
