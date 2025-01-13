<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\GuestHosterService;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;

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
            $chainModel = $hotel->chain;
            $chainSubdomain = $chainModel->subdomain;
            $model = $this->service->inviteToHotel($request, $hotel, $chainSubdomain);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            Log::error('GuestHosterController.inviteToHotelERROR', ['error' => $e]);
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.inviteToHotel');
        }
    }

}
