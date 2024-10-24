<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $colors = $this->colors ? json_decode($this->colors) : null;
        return [
            "colors" => $colors,
            "logo" => $this->logo,
            "name" => $this->name,
            "type_header" => $this->type_header,
            "tonality_header" => $this->tonality_header,
            "chain_id" => $this->chain_id,
        ];
    }
}
