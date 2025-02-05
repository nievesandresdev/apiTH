<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Services\RewardsServices;

class RewardsController extends Controller
{
    public $service;
    function __construct(
        RewardsServices $_RewardsServices
    )
    {
        $this->service = $_RewardsServices;
    }

    public function getRewards (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $rewards = $this->service->getRewards($request, $hotelModel);

            if(!$rewards){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            //
            //$data = FacilityResource::collection($facilities);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $rewards);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], $e->getMessage().' '.self::class . '.getRewards');
        }
    }

    public function storeOrUpdateRewards(Request $request){
        try {
            $hotelModel = $request->attributes->get('hotel');

            $rewards = $this->service->storeOrUpdateRewards($request, $hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'requestCreate' => $request->all(),
                'hotel' => $hotelModel,
                'rewards' => $rewards
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.storeOrUpdateRewards');
        }
    }

    public function storeRewardStay(Request $request){
        try {
            $hotelModel = $request->attributes->get('hotel');

            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'requestCreate' => $request->all(),
            ]);
            //$rewards = $this->service->storeRewardStay($request, $hotelModel);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.storeRewardStay');
        }
    }
}
