<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DataServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;

class ServicesController extends Controller
{
    public $service;

    function __construct(
        DataServices $_DataServices
    )
    {
        $this->service = $_DataServices;
    }

    public function AddColorAndAcronymToGuest (Request $request) {
        try {

            $model = $this->service->AddColorAndAcronymToGuest();
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }

            return $model;

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.AddColorAndAcronymToGuest');
        }
    }

}