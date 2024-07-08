<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;
use App\Models\FacilityHoster;

use App\Services\HotelService;
use App\Services\FacilityService;
use App\Services\ExperienceService;
use App\Services\PlaceService;

use App\Http\Resources\HotelResource;
use App\Http\Resources\FacilityResource;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\PlaceResource;

use App\Utils\Enums\EnumResponse;

use App\Http\Requests\UpdateFacilityOrderRequest;

class FacilityController extends Controller
{
    public $service;
    function __construct(
        FacilityService $_FacilityService
    )
    {
        $this->service = $_FacilityService;
    }

    public function getAll (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $facilities = $this->service->getAll($request, $hotelModel);
            if(!$facilities){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            //
            $data = FacilityResource::collection($facilities);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }


    public function findById ($id, Request $request) {
        try {

            $hotel = $request->attributes->get('hotel');
            $model = $this->service->findById($id,$hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }

            $data = new FacilityResource($model);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findByParams');
        }
    }

    public function updateOrder (UpdateFacilityOrderRequest $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            \DB::beginTransaction();
            $this->service->updateOrder($request->order, $hotelModel);
            \DB::commit();
            $data = $hotelModel->facilities()->orderBy('order')->pluck('id');
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateOrder');
        }
    }

    public function updateVisible (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $facilityHosterModel = $this->service->findById($request->facility_hoster_id, $hotelModel);
            if(!$facilityHosterModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            \DB::beginTransaction();
            $this->service->updateVisible($request, $facilityHosterModel);
            $this->service->syncOrder($hotelModel);
            \DB::commit();
            $data = $facilityHosterModel->refresh();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateOrder');
        }
    }

    public function storeOrUpdate (Request $request) {
        try {
            // $f = FacilityHoster::find($request->id);
            // return $f;
            $hotelModel = $request->attributes->get('hotel');
            \DB::beginTransaction();
            $facilityHosterModel = $this->service->storeOrUpdate($request, $hotelModel);
            // $this->service->processTranslate($request, $facilityHosterModel, $hotelModel);
            $images = $request->images ?? [];
            $this->service->updateImages($images, $facilityHosterModel, $hotelModel);
            \DB::commit();
            $data = [];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.storeOrUpdate');
        }
    }

}
