<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\StayAccessService;

use App\Http\Resources\StaySurveyResource;

use App\Utils\Enums\EnumResponse;

class StayAccessController extends Controller
{
    public $service;

    function __construct(
        StayAccessService $_StayAccessService
    )
    {
        $this->service = $_StayAccessService;
    }

    public function save(Request $request){
        
        try {
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $model = $this->service->save($stayId, $guestId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.save');
        }
    }

}