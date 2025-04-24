<?php

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelMainDataWebappResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        $defaultChatSettingsArray  = defaultChatSettings();
        
        $type = $this->type;
        if (!in_array($this->type, ['hotel', 'at', 'vft','hostal'])) {
            $type = 'hotel';
        }
        return [
            "id" => $this->id,
            "name" => $this->name,
            "type" => $type,
            "zone" => $this->zone,
            "instagram_url" => $this->instagram_url,
            "facebook_url" => $this->facebook_url,
            "pinterest_url" => $this->pinterest_url,
            "show_profile" => $this->show_profile,
            "subdomain" => $this->subdomain,
            "logo" => $this->logo,
            "favicon" => $this->favicon,
            "show_experiences" => $this->show_experiences,
            "instagram_url" => $this->instagram_url,
            "language_default_webapp" => $this->language_default_webapp,
            "x_url" => $this->x_url,
            "show_facilities" => $this->show_facilities,
            "show_places" => $this->show_places,
            "show_confort" => $this->show_confort,
            "show_transport" => $this->show_transport,
            "hidden_categories" => $this->hiddenCategories->pluck('id'),
            "hidden_type_places" => $this->hiddenTypePlaces->pluck('id'),
            "buttons_home" => $this->buttons_home,
            "show_referrals" => $this->show_referrals,
            "show_checkin_stay" => $this->show_checkin_stay,
            "offer_benefits" => $this->offer_benefits,
            "chatSettings" => $this->chatSettings ?? $defaultChatSettingsArray,
            "latitude"=> $this->latitude,
            "longitude"=> $this->longitude,
            "city_id"=> $this->city_id,
            "checkin"=> $this->checkin,
            "checkout"=> $this->checkout,
            "image"=> $this->image,
            "code"=> $this->code,
            "chat_service_enabled"=> $this->chat_service_enabled,
            "checkin_service_enabled"=> $this->checkin_service_enabled
        ];
    }
}
