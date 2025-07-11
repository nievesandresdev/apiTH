<?php

namespace App\Services\Hoster\Hotel;

use App\Models\Customization;
use App\Models\Chain;
use App\Models\Hotel;
use App\Models\ImagesHotels;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Utils\Enums\EnumResponse;
use App\Utils\Enums\EnumsHotel\ConfigHomeSectionsEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class HotelHosterServices
{
    public $chatSettingsService;

    public function __construct(ChatSettingsServices $chatSettingsService) {
        $this->chatSettingsService = $chatSettingsService;
    }

    public function deleteImageByHotel ($hotelId, $imageId) {

        try {
            return ImagesHotels::where('hotel_id',$hotelId)->where('id',$imageId)->delete();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisibility');
        }
    }

    public function toggleChatService ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleChatService');
            }

            if ($enabled) {
                $hotel->chat_service_enabled = true;
            } else {
                $hotel->chat_service_enabled = false;
            }
            $settings = new stdClass();
            $settings->show_guest = $enabled ? true : false;
            $this->chatSettingsService->updateSettings($hotelId, ['show_guest'], $settings);
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleChatService');
        }
    }

    public function toggleCheckinService ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleCheckinService');
            }
            if ($enabled) {
                $hotel->checkin_service_enabled = true;
                $hotel->show_checkin_stay = true;
            } else {
                $hotel->checkin_service_enabled = false;
                $hotel->show_checkin_stay = false;

                // Deshabilitar  el boton "Check-In"
                $hotel->buttons()
                    ->where('name', 'Check-In')
                    ->update(['is_visible' => false]);
            }
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleCheckinService');
        }
    }

    public function toggleReviewsService ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleReviewsService');
            }
            if ($enabled) {
                $hotel->reviews_service_enabled = true;

            } else {
                $hotel->reviews_service_enabled = false;
            }
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleReviewsService');
        }
    }

    public function updateProfileData ($hotelModel, $request, $updateFields = []) {

        // Log::info('delete_imgs '.json_encode($request->delete_imgs));
        // if(is_array($request->delete_imgs) && count($request->delete_imgs)){
        //     foreach ($request->delete_imgs as $img_id) {
        //         ImagesHotels::where("hotel_id", $hotelModel->id)->where("id", $img_id)->delete();
        //     }
        // }
        $hotel = Hotel::find($hotelModel->id);
        // Log::info('updateProfileData', $request->all(), JSON_PRETTY_PRINT);
        in_array('name', $updateFields, true) ? $hotel->name = $request->name : '';
        in_array('type', $updateFields, true) ? $hotel->type = $request->type : '';
        in_array('category', $updateFields, true) ? $hotel->category = $request->category : '';
        in_array('email', $updateFields, true) ? $hotel->email = $request->email : '';
        in_array('address', $updateFields, true) ? $hotel->address = $request->address : '';
        in_array('latitude', $updateFields, true) ? $hotel->latitude = $request->metting_point_latitude : '';
        in_array('longitude', $updateFields, true) ? $hotel->longitude = $request->metting_point_longitude : '';
        in_array('checkin', $updateFields, true) ? $hotel->checkin = $request->checkin : '';
        in_array('checkin_until', $updateFields, true) ? $hotel->checkin_until = $request->checkin_until : '';
        in_array('checkout', $updateFields, true) ? $hotel->checkout = $request->checkout : '';
        in_array('checkout_until', $updateFields, true) ? $hotel->checkout_until = $request->checkout_until : '';
        in_array('description', $updateFields, true) ? $hotel->description = $request->description : '';
        in_array('urlInstagram', $updateFields, true) ? $hotel->instagram_url = $request->urlInstagram : '';
        in_array('urlPinterest', $updateFields, true) ? $hotel->pinterest_url = $request->urlPinterest : '';
        in_array('urlFacebook', $updateFields, true) ? $hotel->facebook_url = $request->urlFacebook : '';
        in_array('urlX', $updateFields, true) ? $hotel->x_url = $request->urlX : '';
        in_array('with_wifi', $updateFields, true) ? $hotel->with_wifi = $request->with_wifi : '';
        in_array('website_google', $updateFields, true) ? $hotel->website_google = $request->website_google : '';
        in_array('show_profile', $updateFields, true) ? $hotel->show_profile = $request->show_profile : '';
        in_array('show_rules', $updateFields, true) ? $hotel->show_rules = $request->show_rules : '';
        in_array('buttons_home', $updateFields, true) ? $hotel->buttons_home = json_encode($request->buttons) : '';
        in_array('contact_email', $updateFields, true) ? $hotel->contact_email = $request->contact_email : '';


        //phones
        if((in_array('phone', $updateFields, true) || count($updateFields) == 0)){
            $hotel->phone = strlen($request->phone) > 4 ? $request->phone : null;
        }
        if((in_array('phone_optional', $updateFields, true) || count($updateFields) == 0)){
            $hotel->phone_optional = strlen($request->phone_optional) > 4 ? $request->phone_optional : null;
        }
        if((in_array('contact_whatsapp_number', $updateFields, true) || count($updateFields) == 0)){
            $hotel->contact_whatsapp_number = strlen($request->contact_whatsapp_number) > 4 ? $request->contact_whatsapp_number : null;
        }
        $hotel->save();
        return $hotel;
    }

    public function getProfileData(int $hotelId, array $fields = []): Hotel
    {
        // Lista blanca de columnas permitidas
        $allowed = [
            'name', 'type', 'category', 'email', 'phone', 'phone_optional',
            'address', 'latitude', 'longitude', 'checkin', 'checkin_until',
            'checkout', 'checkout_until', 'description',
            'instagram_url', 'pinterest_url', 'facebook_url', 'x_url',
            'with_wifi', 'website_google', 'show_profile', 'show_rules',
            'contact_email', 'contact_whatsapp_number', 'show_contact'
        ];

        // Intersectamos para descartar cualquier campo no permitido
        $columns = array_values(array_intersect($allowed, $fields));

        // Siempre incluye el ID para poder instanciar el modelo
        if (! in_array('id', $columns, true)) {
            array_unshift($columns, 'id');
        }

        // Si no solicitan nada concreto, devolvemos todos
        if (empty($columns) || count($columns) === 1 && $columns[0] === 'id') {
            return Hotel::findOrFail($hotelId);
        }

        // Eloquent: SELECT columna1, columna2, ... FROM hotels WHERE id = ?
        return Hotel::select($columns)
                    ->where('id', $hotelId)
                    ->firstOrFail();
    }

    public function toggleShowContact ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleShowContact');
            }
            if ($enabled) {
                $hotel->show_contact = true;
            } else {
                $hotel->show_contact = false;
            }
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleShowContact');
        }
    }

    public function getShowContact ($hotelId) {
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.getShowContact');
        }
        return $hotel->show_contact;
    }


    //order sections
    public function getOrderSections ($hotelId) {
        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.getOrderSections');
        }

        $default = ConfigHomeSectionsEnum::defaultOrderSections();
        return $hotel->order_sections ?? $default;
    }

    public function updateOrderSections ($hotelId, $orderSections) {
        $hotel = Hotel::find($hotelId);
        $hotel->order_sections = $orderSections;
        $hotel->save();
        return $hotel->order_sections;
    }
}
