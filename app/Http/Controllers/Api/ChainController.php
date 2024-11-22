<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChainResource;
use Illuminate\Http\Request;
use App\Services\Hoster\ChainCustomizationServices;
use App\Http\Resources\CustomizationResource;
use App\Http\Resources\HotelCardResource;
use App\Services\ChainService;
use App\Services\HotelService;

use App\Models\Hotel;
use App\Models\Customization;

use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;

class ChainController extends Controller
{
    public $chainServices;
    public $hotelServices;
    public $customizationModel;

    public function __construct(
        ChainService $_chain_services,
        HotelService $_hotel_service,
        Customization $_customization_model
        )
    {
        $this->chainServices = $_chain_services;
        $this->hotelServices = $_hotel_service;
        $this->customizationModel = $_customization_model;
    }

    public function verifySubdomainExist (Request $request) {
        $hotelModel = $request->attributes->get('hotel');

        $chainModel = $hotelModel->chain;

        $data = $this->chainServices->verifySubdomainExist($request->subdomain, $hotelModel, $chainModel);
        $data = [
            "exist" => $data
        ];
        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

    public function getHotelsList (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $hotels = $this->chainServices->getHotelsList($chainSubdomain);
            $hotels = HotelCardResource::collection($hotels);
            if(!$hotels){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotels);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function updateConfigGeneral (Request $request) {
        try {

            $environment = config('app.env');
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);

            $chainModel = $hotelModel->chain;
            if(!$chainModel || !$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            \DB::beginTransaction();

            $subdomain = $request->subdomain_chain;
            $slugHotel = $request->slug_hotel;

            $exitsSubdomain = $this->chainServices->verifySubdomainExist($subdomain, $hotelModel, $chainModel);
            $subdomainIsNotNew = $this->chainServices->verifySubdomainExistInHistory($subdomain, $hotelModel, $chainModel);
            $newSubdomainParam = false;
            if (!$exitsSubdomain && !$subdomainIsNotNew) {
                if ($environment !== 'local') {
                    $r_s = $this->hotelServices->createSubdomainInCloud($subdomain, $environment);
                    $newSubdomainParam = true;
                }
            }
            $this->chainServices->updateSubdomain($subdomain, $chainModel);

            $this->hotelServices->updateSlug($slugHotel, $hotelModel);

            $this->chainServices->updateConfigGeneral($request, $hotelModel);

            \DB::commit();

            $hotelModel->refresh();

            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            \DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateCustomization');
        }
    }

    public function findBySubdomain (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $chain = $this->chainServices->findBySubdomain($chainSubdomain);
            // Log::info('findBySubdomain chain cargada '. json_encode($chain));
            $chain = new ChainResource($chain);
            if(!$chain){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $chain);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findBySubdomain');
        }
    }

    public function getCustomatizacion (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $chain = $this->chainServices->findBySubdomain($chainSubdomain);
            $customization = $chain->customization()->first();
            if(!$customization){
                $customization = $this->customizationModel->valueDefault();
            }
            return $customization;
            $colors = gettype($customization['colors']) == 'string' ? json_decode($customization['colors'], true) : $customization['colors'];
            if ($colors) {
                $colors[0]['contrast_color'] = $colors[0]['contrast'] == '0' ? '#333333' : '#ffffff';
                $colors[1]['contrast_color'] = $colors[1]['contrast'] == '0' ? '#333333' : '#ffffff';
            }
            $customization['colors'] = $colors;
            return bodyResponseRequest(EnumResponse::ACCEPTED, $customization);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getCustomatizacion');
        }
    }

    public function getStaysGuest (Request $request) {
        try {
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $chainId = $request->chainId;
            //$hotel = $request->attributes->get('hotel');
            $stays = $this->chainServices->getStaysGuest($chainId, $guestId, $stayId);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $stays);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getStayByHotel');
        }
    }

}
