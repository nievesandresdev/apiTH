<?php

namespace App\Http\Resources;

use App\Models\ChatHour;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $defaultChatSettingsArray  = defaultChatSettings();
        $chatSettings = ChatSetting::with('languages')->where('hotel_id',$this->id)->first() ?? $defaultChatSettingsArray;

        // $defaultChatHours = defaultChatHours();
        // $chatHours = ChatHour::where('hotel_id',$this->id)->get() ?? $defaultChatHours;


        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "type"=> $this->type,
            "address"=> $this->address,
            "zone"=> $this->zone,
            "category"=> $this->category,
            "image"=> $this->image,
            "images" => $this->images,
            "phone"=> $this->phone,
            "email"=> $this->email,
            "latitude"=> $this->latitude,
            "longitude"=> $this->longitude,
            "checkin"=> $this->checkin,
            "checkout"=> $this->checkout,
            "description"=> $this->description,
            "instagram_url"=> $this->instagram_url,
            "facebook_url"=> $this->facebook_url,
            "pinterest_url"=> $this->pinterest_url,
            "show_profile"=> $this->show_profile,
            "name_short"=> $this->name_short,
            "slug"=> $this->slug,
            // "subscription_active"=> $this->subscription_active,
            "logo"=> $this->logo,
            "url_google"=> $this->url_google,
            "website_google"=> $this->website_google,
            "user_ratings_total" => $this->user_ratings_total,
            "google_maps_place_id"=> $this->google_maps_place_id,
            "favicon" => $this->favicon,
            "show_experiences" => $this->show_experiences,
            "subdomain" => $this->subdomain,
            //"user" => new UserResource($this->user()->first()),
            "translate" => $this->translate,
            "chatSettings" => new ChatSettingResource($chatSettings),
            // "chatHours" => $chatHours,
            "language_default_webapp"=> $this->language_default_webapp,
            "sender_for_sending_sms"=> $this->sender_for_sending_sms,
            "sender_for_sending_email"=> $this->sender_for_sending_email,
            "phone_optional"=> $this->phone_optional,
            "with_wifi"=> $this->with_wifi,
            "checkin_until"=> $this->checkin_until,
            "checkout_until"=> $this->checkout_until,
            "x_url" => $this->x_url,
            "show_facilities" => $this->show_facilities,
            "show_places" => $this->show_places,
            "hidden_categories" => $this->hiddenCategories->pluck('id'),
        ];
    }
}
