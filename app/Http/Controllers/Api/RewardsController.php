<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Reward, RewardStay};
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Services\RewardsServices;
use Illuminate\Support\Facades\Log;
class RewardsController extends Controller
{
    public $service;
    function __construct(
        RewardsServices $_RewardsServices
    )
    {
        $this->service = $_RewardsServices;
    }

    public function getRewards(Request $request) {
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
            $data    = $request->all();
            $code    = $data['code'];
            $webUrl  = $data['webUrl'];
            $hotelId = $data['hotel'];
            // Limpiar la URL base
            $cleanUrl = explode('?', $webUrl)[0];

            // Obtener el código del parámetro "code" en la URL
            parse_str(parse_url($webUrl, PHP_URL_QUERY), $queryParams);
            $codeClean = $queryParams['code'] ?? null; //codigo si la url viene con codigo sino es null

            /* return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'cleanUrl' => $cleanUrl,
                'hotelId' => $hotelId,
                'data' => $data,
                'codeClean' => $codeClean,
            ]); */

            if($codeClean == null){ //si no viene codigo, se busca un reward usado

                $reward = Reward::where('hotel_id', $hotelId) //busca un reward usado
                    ->where('used', true)
                    ->where('type_rewards', 'referent')
                    ->first();

                if($reward){
                    //return bodyResponseRequest(EnumResponse::ACCEPTED, "Reward encontrado y usado1");
                    $rewardStay = RewardStay::where('code', $code)
                        ->where('hotel_id', $hotelId)
                        ->whereHas('reward', function($query) {
                            //$query->where('url', $cleanUrl);
                            $query->where('used', true);
                        })
                        ->where('used', false)
                        ->first();

                    if($rewardStay){
                        if($cleanUrl != $rewardStay->reward->url){
                            $rewardStay->update([
                                'used' => true
                            ]);
                            return bodyResponseRequest(EnumResponse::ACCEPTED, "RewardStay encontrado y actualizado code $code , url $cleanUrl codeClean $codeClean url $rewardStay->reward");
                        }
                    }else{
                        return bodyResponseRequest(EnumResponse::ACCEPTED, "RewardStay no encontrado code $code , url $cleanUrl codeClean $codeClean");
                    }
                }

                //integrar codigo
                $reward = Reward::where('url', $cleanUrl) //busca un reward no usado
                    ->where('used', false)
                    ->where('hotel_id', $hotelId)
                    ->where('type_rewards', 'referent')
                    ->first(); //siempre busca el primero por que un hotel siempre tendra un solo codigo referente, si cambia a que un hotel puede tener varios, esto hay que cambiarlo OJO


                //update used
                if($reward){
                    $reward->update([
                        'used' => true
                    ]);
                    return bodyResponseRequest(EnumResponse::ACCEPTED, "Reward encontrado y actualizado");
                }else{
                    return bodyResponseRequest(EnumResponse::ACCEPTED, "No se encontró un Reward con la url '$cleanUrl' y el codigo '$code' y el type_rewards 'referent' y el used false.");
                }
            }else{ //si viene codigo, se busca un rewardStay con el codigo

                $reward = Reward::where('hotel_id', $hotelId) //busca un reward usado
                    ->where('used', true)
                    ->where('type_rewards', 'referent')
                    ->first();

                if($reward){ //si viene codigo, se busca un rewardStay con el codigo
                    $rewardStay = RewardStay::where('code', $codeClean)
                        ->where('hotel_id', $hotelId)
                        ->whereHas('reward', function($query) use ($cleanUrl) {
                            $query->where('url', $cleanUrl);
                            $query->where('used', true);
                        })
                        ->where('used', false)
                    ->first();

                    return bodyResponseRequest(EnumResponse::ACCEPTED, "rewardStay encontrado");
                }
            }




            /* $rewardStay = RewardStay::where('code', $codeClean)
                ->where('hotel_id', $hotelId)
                ->whereHas('reward', function($query) use ($cleanUrl) {
                    $query->where('url', $cleanUrl);
                })
                ->where('used', false)
            ->first(); */

            /* if($rewardStay){
               $rewardStay->reward()->update([
                'used' => true
               ]);
            } */

            if (!$rewardStay) {
                return bodyResponseRequest(EnumResponse::ACCEPTED, "No se encontró un RewardStay con el código '$codeClean' para el hotel indicado.");
            }else{
                if($cleanUrl != $rewardStay->reward->url){
                    $rewardStay->update([
                        'used' => true
                    ]);
                    //Log::info('sendEmailReferent', ['rewardStay' => $rewardStay]);
                    //$this->service->sendEmailReferent($rewardStay);
                    return bodyResponseRequest(EnumResponse::ACCEPTED, [
                        'message' => 'RewardStay encontrado y enviado',
                    ]);
                }

                return bodyResponseRequest(EnumResponse::ACCEPTED, [
                    'message' => 'RewardStay encontrado pero no coincide la url',
                ]);
            }

         /*    return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'rewardStay' => $rewardStay,
                'cleanUrl' => $cleanUrl,
                'hotelId' => $hotelId,
                'data' => $data,
                'codeClean' => $codeClean,
                'tt' => $tt
            ]); */





            /* return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'request' => $request->all(),
                'code' => $code,
                'webUrl' => $webUrl,
                'hotelId' => $hotelId,
                'cleanUrl' => $cleanUrl,
                'rewardStay' => $rewardStay
            ]); */

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
