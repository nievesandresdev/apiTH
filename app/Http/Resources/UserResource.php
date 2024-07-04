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
        $firstHotelId = $this->hotel()->first() ?? null;

        $phone = $this->profile->phone ?? '';
        $prefix = null;
        $number = null;

        if (!empty($phone)) {
            if (preg_match('/^(\+\d+)\s(.+)/', $phone, $matches)) {
                $prefix = $matches[1];
                $number = $matches[2];
            } else {
                $number = $phone;
            }
        }

        if (empty($phone)) {
            $prefix = null;
            $number = null;
        }

        return [
            'name' => $this->profile->firstname ?? '',
            'lastname' => $this->profile->lastname ?? '',
            'email' => $this->email,
            'prefix' => $prefix,
            'phone' => $number,
            'role' => $this->getRoleName(),
            'last_session' => $this->last_session,
            'created_at' => $this->created_at,
            'color' => $this->color,
            'hotels' => $this->hotel->map(function ($hotel) {
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
                    'permissions' =>  $hotel->pivot->permissions,
                ];
            }),
            'current_hotel' => new HotelResource($firstHotelId),
            'current_subdmain_hotel' => $firstHotelId?->subdomain
        ];
    }
}

