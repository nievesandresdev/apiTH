<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatSettingResource;
use App\Services\Hoster\Chat\ChatSettingsServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Services\LanguageServices;
use App\Services\HotelService;
use Illuminate\Support\Facades\DB;

class ChatSettingsController extends Controller
{
    public $service;
    public $languageService;
    public $hotelService;


    function __construct(
        ChatSettingsServices $_ChatSettingsServices,
        LanguageServices $_LanguageServices,
        HotelService $_HotelService,
    )
    {
        $this->service = $_ChatSettingsServices;
        $this->languageService = $_LanguageServices;
        $this->hotelService = $_HotelService;

    }

    // private function get_settings(){
    //     $settings = currentHotel()->chatSettings()->first();
    //     if(!$settings){
    //         $settings = defaultChatSettings();
    //     }else{
    //         $settings->load('languages');
    //     }
    //     return $settings;
    // }

    public function getAll(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $model = new ChatSettingResource($model,['email_notify_new_message_to','email_notify_pending_chat_to','email_notify_not_answered_chat_to']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function updateNotificationsEmail(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ['email_notify_new_message_to','email_notify_pending_chat_to','email_notify_not_answered_chat_to'], $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateNotificationsEmail');
        }
    }

    public function getAllsAndChatHours(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $settings = $this->service->getAll($hotel->id);

            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'settings' => $settings,
                'chatHours' => $this->hotelService->getChatHours($hotel->id,true)
            ]);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllsAndChatHours');
        }
    }



    public function searchLang(Request $request){

        try {
            $data = $this->languageService->search_lang(request());
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.search_lang');
        }

    }

    public function storeGeneralSetting(Request $request){
        $hotel = $request->attributes->get('hotel');

        try {
            $model = $this->service->updateSettings($hotel->id, ['name','show_guest','languages'], $request);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.storeGeneralSetting');
        }

        return $hotel;
    }

    public function updateAvailability(Request $request){
        $hotel = $request->attributes->get('hotel');

        try {
            $availability = $request->hours;
            $model = $this->service->updateAvailability($availability,$hotel->id);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateAvailability');
        }

    }

    public function updateResponses(){
        try {
            $hotel = request()->attributes->get('hotel');

            /* return [
                'hotel' => $hotel,
                'request' => request()->all()
            ]; */
            $model = $this->service->updateSettings($hotel->id,
            [
                'show_guest',
                'not_available_msg',
                'not_available_show',
                'first_available_msg',
                'first_available_show',
                'second_available_msg',
                'second_available_show',
                'three_available_msg',
                'three_available_show'

            ], request());

            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateResponses');
        }
    }

}
