<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckinSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "hotel_id" => $this->hotel_id ?? null,
            "succes_message" => $this->succes_message,
            "first_step" => $this->first_step,
            "second_step" => $this->second_step,
            "show_prestay_query" => $this->show_prestay_query,
        ];
    }
}
