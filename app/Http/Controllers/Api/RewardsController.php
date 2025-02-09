<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Reward, RewardStay};
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

    public function storeRewardStay(Request $request)
    {
        try {
            // 1. Extraer datos del request (se asume que 'code', 'webUrl' y 'hotel' siempre vienen)
            $data    = $request->all();
            $code    = $data['code'];
            $webUrl  = $data['webUrl'];
            $hotelId = $data['hotel'];

            $cleanUrl = explode('?', $webUrl)[0];

            $rewardStay = RewardStay::where('code', $code)
                ->where('hotel_id', $hotelId)
                ->whereHas('reward', function($query) use ($cleanUrl) {
                    $query->where('url', $cleanUrl);
                })
                ->first();

            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'code' => $code,
                'webUrl' => $webUrl,
                'hotelId' => $hotelId,
                'cleanUrl' => $cleanUrl,
                'rewardStay' => $rewardStay
            ]);

            $cleanUrl = explode('?', $webUrl)[0];

            $rewardStay = RewardStay::where('code', $code)
                ->where('hotel_id', $hotelId)
                ->whereHas('reward', function($query) use ($cleanUrl) {
                    $query->where('url', $cleanUrl);
                })
                ->first();

            if (!$rewardStay) {
                return bodyResponseRequest(EnumResponse::ERROR, "No se encontró un RewardStay con el código '$code' para el hotel indicado.");
            }

            $reward = $rewardStay->reward;


            // 6. Retornar respuesta exitosa.
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'message'    => 'El código del Reward se ha aplicado correctamente.',
                'reward'     => $reward,
                'rewardStay' => $rewardStay,
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, ['message' => $e->getMessage()], [], $e->getMessage());
        }
    }




    public function createCodeReferent(Request $request){
        try {
            $hotelModel = $request->attributes->get('hotel');

            $code = $this->service->createCodeReferent($request, $hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $code);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createCodeReferent');
        }
    }
}
