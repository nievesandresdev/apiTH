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

    public function storeRewardStay(Request $request)
    {
        try {
            // 1. Extraer datos del request (se asume que 'code', 'webUrl' y 'hotel' siempre vienen)
            $data    = $request->all();
            $code    = $data['code'];
            $webUrl  = $data['webUrl'];
            $hotelId = $data['hotel'];

            // 2. Parsear la webUrl para obtener la URL base (sin el query string '?code=...')
            $parsedUrl = parse_url($webUrl);
            if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
                return bodyResponseRequest(EnumResponse::ERROR, "El webUrl proporcionado no es una URL válida.");
            }
            $baseWebUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . (isset($parsedUrl['path']) ? $parsedUrl['path'] : '');
            $baseWebUrl = rtrim($baseWebUrl, '/');

            // 3. Buscar en la base de datos el registro de RewardStay correspondiente al código y hotel.
            $rewardStay = \App\Models\RewardStay::where('code', $code)
                ->where('hotel_id', $hotelId)
                ->first();

            if (!$rewardStay) {
                return bodyResponseRequest(EnumResponse::ERROR, "No se encontró un RewardStay con el código '$code' para el hotel indicado.");
            }

            // 4. Obtener el Reward asociado y validar que la URL almacenada coincida con la URL base obtenida.
            $reward = $rewardStay->reward;
            if (!$reward) {
                return bodyResponseRequest(EnumResponse::ERROR, "No se encontró el Reward asociado al RewardStay.");
            }

            $rewardUrl = rtrim($reward->url, '/');
            if ($baseWebUrl !== $rewardUrl) {
                return bodyResponseRequest(EnumResponse::ERROR, "La URL proporcionada ($baseWebUrl) no coincide con la URL del Reward ($rewardUrl).");
            }

            // 5. Verificar si el Reward ya fue utilizado.
            if ($reward->used) {
                return bodyResponseRequest(EnumResponse::ERROR, "El Reward ya ha sido utilizado.");
            }

            // Marcar el Reward como usado.
            $reward->used = true;
            $reward->save();

            // 6. Retornar respuesta exitosa.
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'message'    => 'El código del Reward se ha aplicado correctamente.',
                'reward'     => $reward,
                'rewardStay' => $rewardStay,
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), [], self::class . '.storeRewardStay');
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
