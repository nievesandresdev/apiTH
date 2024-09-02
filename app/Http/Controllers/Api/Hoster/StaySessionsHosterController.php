<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Stay\StaySessionServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Database\Seeders\run;

class StaySessionsHosterController extends Controller
{
    public $service;

    function __construct(
        StaySessionServices $_StaySessionServices
    )
    {
        $this->service = $_StaySessionServices;
    }

    //sessions
    public function getSessions(Request $request){
        try {
            $model = $this->service->getSessions($request->stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getSessions');
        }
    }
    
    public function createSession(Request $request){
        try {
            $model = $this->service->createSession($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createSession');
        }
    }

    public function deleteSession(Request $request){
        try {
            // Log::info('deleteSession');
            $userEmail = $request->userEmail;
            $field = $request->field;
            $stayId = $request->stayId;

            $request->validate([
                'userEmail' => 'string',
                'field' => 'string',
                'stayId' => 'integer',
            ]);
            
            $model = $this->service->deleteSession($stayId, $userEmail);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteSession');
        }
    }

    public function deleteSessionWithApiKey(Request $request){
        try {
            // Log::info('deleteSessionWithApiKey');
            $userEmail = $request->query('userEmail');
            $stayId = $request->query('stayId');
            $request->validate([
                'xKeyApi' => 'string',
                'userEmail' => 'string',
                'field' => 'string',
                'stayId' => 'string',
            ]);

            $xKeyApi = $request->query('xKeyApi');
            $envApiKey = config('app.x_key_api');
            if($xKeyApi !== $envApiKey){
                    
            }
            $this->service->deleteSession($stayId, $userEmail);
            Log::info('session de hoster estancia eliminada con existo stayId:'. $stayId);
        } catch (\Exception $e) {
            Log::error('error al eliminar session de hoster estancia '. json_encode($e));
        }
    }

    public function deleteSessionByHotelAndEmail(Request $request){
        try {
            Log::info('deleteSessionByHotelAndEmail');
            $userEmail = $request->userEmail;
            $hotel = $request->attributes->get('hotel');
            $request->validate([
                'userEmail' => 'string|required',
            ]);
            
            //encontrar stayId mediante userEmail
            $model = null;
            $stay = $this->service->findSessionByHotelAndEmail($hotel->id, $userEmail);
            // Log para diagnosticar qué se está recibiendo
            Log::info('Type of $stay: ' . gettype($stay));
            Log::info('Content of $stay: ' . json_encode($stay));

            if($stay){
                Log::info('entro');
                $stayId = $stay->id;
                Log::info('llego?');
                $model = $this->service->deleteSession($stayId, $userEmail);
            }

            if(!$model || !$stay){
                $error = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $error);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteSessionByHotelAndEmail');
        }
    }

}
