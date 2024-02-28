<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => $this->images->first(),
            'images' => $this->images,
            'title' => $this->translate->title,
            'description' => $this->translate->description,
            'schedule' => $this->translate->schedule,
        ];
    }
}
