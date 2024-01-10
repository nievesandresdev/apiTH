<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\GuestService;
class GuestController extends Controller
{
    public $service;

    function __construct(
        GuestService $_StayService
    )
    {
        $this->service = $_StayService;
    }

    public function findById ($id) {
        try {
            $model = $this->service->findById($id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findById');
        }
    }

   

}
