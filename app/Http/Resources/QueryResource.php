<?php

namespace App\Http\Resources;

use App\Models\StayAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "period"=> $this->period,
            "stay_id"=> $this->stay_id,
            "guest_id"=> $this->guest_id,
            "answered" => $this->answered,
            "qualification" => $this->qualification,
            "comment" => $this->comment
        ];
    }
}
