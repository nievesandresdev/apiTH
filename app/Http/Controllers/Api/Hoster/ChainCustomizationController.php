<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Hoster\ChainCustomizationServices;
use App\Http\Resources\CustomizationResource;

use App\Utils\Enums\EnumResponse;

class ChainCustomizationController extends Controller
{
    public function __construct(
        ChainCustomizationServices $_chain_customization_services
        )
    {
        $this->chainCustomizationServices = $_chain_customization_services;
    }

    public function update (Request $request) {
        $hotelModel = $request->attributes->get('hotel');

        $chainModel = $hotelModel->chain;

        $data = $this->chainCustomizationServices->createOrUpdate($request, $hotelModel, $chainModel);

        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

    public function findOne (Request $request) {
        $hotelModel = $request->attributes->get('hotel');
        
        $chainModel = $hotelModel->chain;

        $customizationModel = $chainModel->customization;
        if(!$customizationModel){
            $data = [
                'message' => __('response.bad_request_long')
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }

        $data = new CustomizationResource($customizationModel);

        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

    public function updateConfigGeneral (Request $request) {
        try {
            $environment = env('APP_ENV');
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            \DB::beginTransaction();

            $subdomainChain = $request->subdomain_chain;
            $exitsSubdomain = $this->service->verifySubdomainExistPerHotel($subdomain, $hotelModel);
            $subdomainIsNotNew = $this->service->verifySubdomainExist($subdomain, $hotelModel);
            $newSubdomainParam = false;
            if (!$exitsSubdomain && !$subdomainIsNotNew) {
                if ($environment !== 'LOCAL') {
                    $r_s = $this->service->createSubdomainInCloud($subdomain, $environment);
                    $newSubdomainParam = true;
                }
            }
            $this->service->updateSubdomain($subdomain, $hotelModel);

            $this->service->updateCustomization($request, $hotelModel);


            \DB::commit();

            $hotelModel->refresh();

            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            \DB::rollback();
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateCustomization');
        }
    }

}
