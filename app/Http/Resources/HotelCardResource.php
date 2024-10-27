<?php

namespace App\Http\Resources;

use App\Models\ChatHour;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HotelCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "category" => $this->category,
            "name"=> $this->name,
            "address"=> $this->address,
            "zone"=> $this->zone,
            "image"=> $this->image,
            "subdomain"=> $this->subdomain,
        ];

    }
}
