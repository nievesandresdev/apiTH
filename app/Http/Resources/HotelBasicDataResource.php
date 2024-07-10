<?php

namespace App\Http\Resources;

use App\Models\ChatHour;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "subdomain"=> $this->subdomain,
            "type"=> $this->type,
            "zone"=> $this->zone,
            "image"=> $this->image,
            "subscribed"=> $this->subscription_active ? $user->subscribed($this->subscription_active) : false,
            "with_notificartion" => false,
        ];
    }
}
