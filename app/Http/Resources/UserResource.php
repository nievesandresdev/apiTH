<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HotelResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Buscar el hotel con is_default = 1
        $defaultHotel = $this->hotel()->wherePivot('is_default', 1)->first();

        // Si no se encuentra un hotel con is_default = 1, seleccionamos el primero
        $firstHotelId = $defaultHotel ?? $this->hotel()->first();

        $phone = $this->profile->phone ?? '';

        //$is_subscribed =$this->parent->subscriptions()->where(['name' => $this->subscription_active, 'stripe_status' => 'active'])->exists();

        return [
            'id' => $this->id,
            'name' => $this->profile->firstname ?? '',
            'lastname' => $this->profile->lastname ?? '',
            'email' => $this->email,
            'phone' => $phone,
            'role' => 'Associate',
            'owner' => $this->owner,
            'last_session' => $this->last_session,
            'created_at' => $this->created_at,
            'color' => $this->color,
            'trial' => $this->parent?->trial_duration,
            'hotels' => $this->hotel->map(function ($hotel){
                return [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'name_origin' => $hotel->name_origin,
                    'type' => $hotel->type,
                    'address' => $hotel->address,
                    'zone' => $hotel->zone,
                    'category' => $hotel->category,
                    'image' => $hotel->image,
                    'phone' => $hotel->phone,
                    'email' => $hotel->email,
                    'latitude' => $hotel->latitude,
                    'longitude' => $hotel->longitude,
                    'description' => $hotel->description,
                    'instagram_url' => $hotel->instagram_url,
                    'facebook_url' => $hotel->facebook_url,
                    'pinterest_url' => $hotel->pinterest_url,
                    'slug' => $hotel->slug,
                    'name_short' => $hotel->name_short,
                    'subdomain' => $hotel->subdomain,
                    //'subscribed' => $is_subscribed,
                    //'permissions' =>  $hotel->pivot->permissions,
                ];
            }),
            'parent_hotels' => $this->parent_id ? $this->parent->hotel->map(function ($hotel) {
            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'name_origin' => $hotel->name_origin,
                'type' => $hotel->type,
                'address' => $hotel->address,
                'zone' => $hotel->zone,
                'category' => $hotel->category,
                'image' => $hotel->image,
                'phone' => $hotel->phone,
                'email' => $hotel->email,
                'latitude' => $hotel->latitude,
                'longitude' => $hotel->longitude,
                'description' => $hotel->description,
                'instagram_url' => $hotel->instagram_url,
                'facebook_url' => $hotel->facebook_url,
                'pinterest_url' => $hotel->pinterest_url,
                'slug' => $hotel->slug,
                'name_short' => $hotel->name_short,
                'subdomain' => $hotel->subdomain,
            ];
        }) : null,
            'permissions' => $this->permissions,
            'current_hotel' => new HotelResource($firstHotelId),
            'current_subdmain_hotel' => $firstHotelId?->subdomain
        ];
    }
}

