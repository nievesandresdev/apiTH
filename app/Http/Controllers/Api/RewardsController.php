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

            // 2. Buscar en la base de datos el registro de RewardStay correspondiente al código y hotel.
            $rewardStay = \App\Models\RewardStay::where('code', $code)
                ->where('hotel_id', $hotelId)
                ->first();


            // Obtener el Reward relacionado.
            $reward = $rewardStay->reward;


            // 3. Parsear la webUrl recibida para extraer su base (scheme, host y path)
            $parsedUrl = parse_url($webUrl);
            if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
                throw new \Exception("El webUrl proporcionado no es una URL válida.");
            }
            // Reconstruir la URL base sin el query string
            $baseWebUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host']
                        . (isset($parsedUrl['path']) ? $parsedUrl['path'] : '');
            // Quitar posibles barras finales para evitar discrepancias
            $baseWebUrl = rtrim($baseWebUrl, '/');
            $rewardUrl  = rtrim($reward->url, '/');

            // 4. Validar que la URL base del request comience con la URL del Reward.
            // Esto evita ejecutar la lógica (y consultar la BD en exceso) cuando se refresca en otra página.
            if (strpos($baseWebUrl, $rewardUrl) !== 0) {
                return bodyResponseRequest(EnumResponse::ACCEPTED, [
                    'message' => 'La URL no coincide con la URL base del Reward. No se realiza acción en la base de datos.'
                ]);
            }

            // 5. Verificar si el Reward ya fue utilizado.
            if ($reward->used) {
                throw new \Exception("El Reward ya ha sido utilizado.");
            }

            // Marcar el Reward como usado.
            $reward->used = true;
            $reward->save();

            // Retornar respuesta exitosa.
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'message'    => 'El código del Reward se ha aplicado correctamente.',
                'reward'     => $reward,
                'url'        => $baseWebUrl,
                //'rewardStay' => $rewardStay,
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), ['message' => $e->getMessage()], self::class . '.storeRewardStay');
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
