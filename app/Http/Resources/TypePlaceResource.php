<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\CategoriPlaceResource;

class TypePlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "icon" => $this->icon,
            "translation_current" => $this->translate[localeCurrent()],
            "categori_places" => CategoriPlaceResource::collection($this->categoriPlaces()->where(['active'=>true, 'show'=>true])->get())
        ];
    }
}
