<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\ActivityDetailResource;

class ExperienceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hotel = $request->attributes->get('hotel');
        $metting_point_reference = $this['translation']['metting_point_reference'];
        $end_point_reference = $this['translation']['end_point_reference'];

        $metting_point_latitude='';
        $metting_point_longitude='';
        $end_point_latitude='';
        $end_point_longitude='';

        $location = $this['location'] ?? null;

        /*$location_metting = $location[0] ?? null;
        if ($location_metting) {
            $location_provider_metting = $location[0]['provider'] ?? null;
            if ($location_provider_metting == 'GOOGLE') {
                $geometry = $location[0]['result']['geometry'] ?? null;
                $metting_point_latitude = $geometry['location']['lat']??null;
                $metting_point_longitude = $geometry['location']['lng']??null;
            } else {
                $center = $location[0]['center'] ?? null;
                $metting_point_latitude = $center['latitude'] ?? null;
                $metting_point_longitude = $center['longitude'] ?? null;
            }  
        }

        $location_end = $location[1] ?? null;
        if ($location_end) {
            $location_provider_end = $location[1]['provider'] ?? null;
            if ($location_provider_end == 'GOOGLE') {
                $geometry = $location[1]['result']['geometry'] ?? null;
                $end_point_latitude = $geometry['location']['lat']??null;
                $end_point_longitude = $geometry['location']['lng']??null;
            } else {
                $center = $location[1]['center'] ?? null;
                $metting_point_latitude = $center['latitude'] ?? mull;
                $metting_point_longitude = $center['longitude'] ?? null;
            }        
        }*/

        // $end_point_latitude = $this['translation']['end_point_latitude'] ?? null;
        // $end_point_longitude = $this['translation']['end_point_longitude'] ?? null;
        // $metting_point_latitude = $this['translation']['metting_point_latitude'] ?? null;
        // $metting_point_longitude = $this['translation']['metting_point_longitude'] ?? null;

        $recomendation = $hotel ? $this->recomendations()->where('hotel_id', $hotel->id)->with('hotel')->first() : null;
        return [
            'id' => $this->id,
            'images' => $this->images,
            'type' => $this->type,
            'status' => $this->status,
            'destacado' => $this->destacado,
            // 'des_multi' => $this->des_multi,
            'order' => $this->order,
            // 'name_api',
            'slug' => $this->slug,
            'api_short_id' => $this->api_short_id,
            'show' => $this->show,
            'recomend' => $this->recomend,
            'select' => $this->select,
            'from_price' => $this->from_price,
            'reviews' => $this->reviews,
            'recomendations' => $recomendation,
            'recomendation_language_current' => $recomendation ? $recomendation->translationLanguageCurrent() : null,
            'product_featured' => $hotel ? $this->productFeatured()->where('hotel_id', $hotel->id)->with('hotel')->first() : null,
            'title' => $this['translation']['title'],
            'description' => $this['translation']['description'],
            // 'metting_point_latitude' => $metting_point_latitude,
            // 'metting_point_longitude' => $metting_point_longitude,
            // 'end_point_latitude' => $end_point_latitude,
            // 'end_point_longitude' => $end_point_longitude,
            'metting_point_latitude' => $this['translation']['metting_point_latitude'],
            'end_point_latitude' => $this['translation']['end_point_latitude'],
            'metting_point_longitude' => $this['translation']['metting_point_longitude'],
            'end_point_longitude' => $this['translation']['end_point_longitude'],
            'metting_point_reference' => $this['translation']['metting_point_reference'],
            'end_point_reference' => $this['translation']['end_point_reference'],
            'location' => $this['location'],
            'include_experince' => $this['translation']['include_experince'],
            'not_include_experince' => $this['translation']['not_include_experince'],
            'other_information' => $this['translation']['recommendations'],
            'rules' => $this['translation']['rules'],
            'cancellation_policy' => $this['translation']['cancellation_policy'], 
            'hours_reservation' => $this['translation']['hours_reservation'],
            'language_experince' => $this['translation']['language_experince'],
            'city_experince' => $this['translation']['city_experince'],
            'duration' => $this['translation']['duration']
        ];
    }
}
