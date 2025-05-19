<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HotelButton;
use App\Models\Hotel;
use App\Utils\Enums\EnumResponse;
use App\Services\HotelButtonsService;

class HotelButtonsController extends Controller
{
    public $service;
    public function __construct(HotelButtonsService $_HotelButtonsService) {
        $this->service = $_HotelButtonsService;
    }

    public function getButtons(Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $buttons = $this->service->getHotelButtons($hotelModel);

            //return bodyResponseRequest(EnumResponse::ACCEPTED, ['hotel' => $hotelModel, 'buttons' => $hotelModel->buttons()->get()]);

            if(!$buttons){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            //
            return bodyResponseRequest(EnumResponse::ACCEPTED, ['data' => $buttons, 'hotel' => $hotelModel]);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], $e->getMessage().' '.self::class . '.getButtons');
        }
    }

    public function updateOrder(Request $request)
    {
        $hotelModel = $request->attributes->get('hotel');


        $this->service->updateButtonsOrder($request->visible, $request->hidden);
        $buttons = $this->service->getHotelButtons($hotelModel);

        return bodyResponseRequest(EnumResponse::ACCEPTED, ['data' => $buttons]);
    }

    public function updateButtonVisibility(Request $request)
    {
        $this->service->updateButtonVisibility($request->id);
        return bodyResponseRequest(EnumResponse::ACCEPTED, ['status' => 'success']);
    }
}
