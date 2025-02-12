<?php

namespace App\Http\Resources;

use App\Models\StayAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $uniqueAccessesCount = StayAccess::where('stay_id', $this->id)
                     ->distinct('guest_id')
                     ->count(['guest_id']);

        return [
            "id"=> $this->id,
            "check_out"=> $this->check_out,
            "check_in"=> $this->check_in,
            "hotel_id"=> $this->hotel_id,
            "room" => $this->room,
            "language" => $this->language,
            "number_guests" => $this->number_guests,
            "hour_checkin" => $this->hour_checkin,
            "hour_checkout" => $this->hour_checkout,
            "uniqueAccessesCount" => $uniqueAccessesCount,
            "hotelSubdomain" => $this->hotel->subdomain,
            "middle_reservation" => $this->middle_reservation,
            "guestIdCreator" => $this->guest_id,
        ];
    }
}
