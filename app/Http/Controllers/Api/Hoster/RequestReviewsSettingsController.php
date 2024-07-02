<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\RequestReviews\RequestReviewsSettingsServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class RequestReviewsSettingsController extends Controller
{
    public $service;

    function __construct(
        RequestReviewsSettingsServices $_RequestReviewsSettingsServices
    )
    {
        $this->service = $_RequestReviewsSettingsServices;
    }

    public function updateData(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ['msg_title','msg_text','otas_enabled','request_to'], $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateData');
        }
    }

}
