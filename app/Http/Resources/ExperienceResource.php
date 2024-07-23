<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use App\Http\Resources\ActivityDetailResource;

class ExperienceResource extends JsonResource
{

    
    public function toArray(Request $request): array
    {
        $lang = ucfirst(localeCurrent());

        $modelHotel = $request->attributes->get('hotel');
        $distance = null;
        if($this->distance){
            $distance = round($this->distance / 1000, 2);
        }

        $isVisible = $this->toggleableHotels()->where('hotel_id', $modelHotel->id)->exists() && !$this->productHidden()->where('hotel_id', $modelHotel->id)->exists();
        $productFeatured = $this->productFeatured()->where('hotel_id', $modelHotel->id)->first();
        $toggleProduct = $this->toggleableHotels()->where('hotel_id', $modelHotel->id)->first();
        $productHidden = $this->productHidden()->where('hotel_id', $modelHotel->id)->first();
        return [
            'id' => $this->id,
            'image' => $this->images()->orderBy('id','ASC')->first(),
            'status' => $this->status,
            'destacado' => $this->destacado,
            'distance' => $distance,
            'slug' => $this->slug,
            'recomend' => $this->recomend,
            'select' => $this->select,
            'from_price' => $this->from_price,
            'reviews' => $this->reviews,
            'is_visible' => boolval($isVisible),
            'recomendations' => $this->recomendations()->where('hotel_id', $modelHotel->id)->first(),
            'product_featured' => $productFeatured,
            'product_hidden' => $productHidden,
            'featured' => !empty($productFeatured),
            // activities
            'title' => $this['translation']['title'],
            'cancellation_policy' => $this['translation']['cancellation_policy'], 
            'hours_reservation' => $this['translation']['hours_reservation'],
            'language_experince' => $this['translation']['language_experince'],
            'city_experince' => $this['translation']['city_experince'],
            'slug_city' => Str::slug($this['translation']['city_experince']),
            'duration' => $this['translation']['duration'],
            'position' => $toggleProduct?->pivot->position,
            'toggle_product_id' => $toggleProduct?->pivot->id,
        ];
    }
}