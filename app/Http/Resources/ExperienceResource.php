<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\ActivityDetailResource;

class ExperienceResource extends JsonResource
{

    
    public function toArray(Request $request): array
    {
        $lang = ucfirst(localeCurrent());

        $modelHotel = $request->attributes->get('hotel');
        
        return [
            'id' => $this->id,
            'images' => $this->images->take(1),
            'status' => $this->status,
            'destacado' => $this->destacado,
            'slug' => $this->slug,
            'recomend' => $this->recomend,
            'select' => $this->select,
            'from_price' => $this->from_price,
            'reviews' => $this->reviews,
            'recomendations' => $this->recomendations()->where('hotel_id', $modelHotel->id)->first(),
            'product_featured' => $this->productFeatured()->where('hotel_id', $modelHotel->id)->first(),
            // activities
            'title' => $this['translate']['title'],
            'cancellation_policy' => $this['translate']['cancellation_policy'], 
            'hours_reservation' => $this['translate']['hours_reservation'],
            'language_experince' => $this['translate']['language_experince'],
            'city_experince' => $this['translate']['city_experince'],
            'duration' => $this['translate']['duration'],
        ];
    }
}