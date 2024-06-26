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
            'image' => !empty($this->images) ? $this->images->first() : null,
            'images' => $this->images,
            'title' => $this->translate->title ?? null,
            'description' => $this->translate->description ?? null,
            'schedule' => $this->translate->schedule ?? null
        ];
    }
}
