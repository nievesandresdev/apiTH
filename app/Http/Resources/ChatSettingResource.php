<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            "name"=> $this->name,
            "languages"=> $this->languages,
            "show_guest"=> $this->show_guest,
            "not_available_show"=> $this->not_available_show,
            "not_available_msg"=> $this->not_available_msg,
            "hotel_id"=> $this->hotel_id ?? null,
        ];
    }
}
