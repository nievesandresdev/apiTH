<?php

namespace App\Http\Resources;

use App\Models\CategoriPlaces;
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

        $distance = null;
        if($this->distance){
            $distance = round($this->distance / 1000, 2);
        }

        $firstCategoryPlace = CategoriPlaces::where('type_places_id',$this->typePlaces->id)->first();
        $firstCategoryPlace = $firstCategoryPlace->id ?? null;
        $recomendation = $modelHotel ? $this->recomendations()->where('hotel_id', $modelHotel->id)->with('hotel')->first() : null;
        return [
            'id' => $this->id,
            'title' => $this->translatePlace->title ?? '',
            'description' => $this->translatePlace->description ?? '',
            'datos_interes' => $this->translatePlace->datos_interes ?? '',
            'address' => $this->address,
            'metting_point_latitude' => $this->metting_point_latitude,
            'metting_point_longitude' => $this->metting_point_longitude,
            'place_images' => $this->images,
            'distance' => $distance,
            'type_place' => $this->typePlaces,
            'categori_place' => new CategoriPlaces($this->categoriPlaces),
            'range_prices' => $this->range_prices,
            'type_cuisine' => $this->type_cuisine,
            'url_menu' => $this->url_menu,
            'web_link' => $this->web_link,
            'phone_wheretoeat' => $this->phone_wheretoeat,
            'email_wheretoeat' => $this->email_wheretoeat,
            'range_numeric_prices' => $this->range_numeric_prices,
            'diet_specifications' => $this->diet_specifications,
            'city' => \Str::slug($this->city_places),
            'cityName' => $this->city_places,
            'recommendation_admin' => $this->recommendation_admin,
            'categori_places_id' => $this->categori_places_id,
            'num_reviews' => $this->num_reviews,
            'num_stars' => $this->num_stars,
            'featured' => $this->featured,
            'selection_admin' => $this->selection_admin,
            'category' => $this->categoriPlaces()->first()->name ?? null,
            'first_category_place' => $firstCategoryPlace,
            'place_featured' => $this->placeFeatured()->where('hotel_id', $modelHotel->id)->first(),
            'recommended' => $this->recomendations()->where('hotel_id', $modelHotel->id)->first(),
            'recomendations' => $recomendation,
            'recomendation_language_current' => $recomendation ? $recomendation->translationLanguageCurrent() : null,
        ];


    }
}
