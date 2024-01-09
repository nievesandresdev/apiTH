<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = ucfirst(localeCurrent());

        $modelHotel = $request->attributes->get('hotel');

        return [
            'id' => $this->id,
            'title' => $this->translatePlace->title ?? '',
            'description' => $this->translatePlace->description ?? '',
            'datos_interes' => $this->translatePlace->datos_interes ?? '',
            'place_images' => $this->images->take(1),
            'type_place' => $this->typePlaces,
            'city' => \Str::slug($this->city_places),
            'recommendation_admin' => $this->recommendation_admin,
            'categori_places_id' => $this->categori_places_id,
            'num_reviews' => $this->num_reviews,
            'num_stars' => $this->num_stars,
            'featured' => $this->featured,
            'selection_admin' => $this->selection_admin,
            'category' => $this->categoriPlaces()->first()->name ?? null,
            'place_featured' => $this->placeFeatured()->where('hotel_id', $modelHotel->id)->first(),
            'recommended' => $this->recomendations()->where('hotel_id', $modelHotel->id)->first(),
            'recomendations' => $this->recomendations()->where('hotel_id', $modelHotel->id)->first(),
        ];


    }
}
