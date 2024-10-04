<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Services\Hoster\FacilityHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Database\Seeders\run;

class FacilityHosterController extends Controller
{
    public $service;

    function __construct(
        FacilityHosterServices $_FacilityHosterServices
    )
    {
        $this->service = $_FacilityHosterServices;
    }

    public function getAll (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $dataModel = $this->service->getAll($request, $hotelModel);
            
            // Log::info("datamodel ". json_encode($dataModel));
            if(!$dataModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            
            $facilities = FacilityResource::collection($dataModel['facilities']);
            $data =  [
                "facilities" => $facilities,
                "totalCount" => $dataModel['totalCount'],
                "totalVisibleCount" => $dataModel['totalVisibleCount'],
                "totalHiddenCount" => $dataModel['totalHiddenCount']
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }
    
    
}
