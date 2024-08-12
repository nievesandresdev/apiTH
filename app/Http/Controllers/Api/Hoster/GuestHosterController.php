<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\GuestHosterService;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;

class GuestHosterController extends Controller
{
    public $service;

    function __construct(
        GuestHosterService $_GuestHosterService
    )
    {
        $this->service = $_GuestHosterService;
    }

    public function inviteToHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->inviteToHotel($request, $hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.inviteToHotel');
        }
    }

}
