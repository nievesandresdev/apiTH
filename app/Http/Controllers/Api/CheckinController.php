<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CheckinSettingsResource;
use App\Services\CheckinServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public $service;

    function __construct(
        CheckinServices $_CheckinServices
    )
    {
        $this->service = $_CheckinServices;
    }

    public function getAllSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAllSettings($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new CheckinSettingsResource($model,['succes_message']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGeneralSettings');
        }
    }

    public function sendPassportImage(Request $request){
        try {
            // 1. Recibir el archivo
            if (!$request->hasFile('passportImage')) {
                return response()->json([
                    'ok' => false,
                    'error' => 'No file uploaded',
                ], 400);
            }
    
            $file = $request->file('passportImage'); 
            // Aquí obtienes un \Illuminate\Http\UploadedFile
    
            // 2. Leer su contenido (Byte Stream) para enviarlo a Azure
            $fileContent = file_get_contents($file->getRealPath());
            $mimeType = $file->getMimeType();
            // Ej: "image/jpeg", "image/png", etc.
    
            // 3. Llamar a la API de Azure Form Recognizer
            //    (Puedes usar Guzzle, Http::, etc.)
            $azureResponse = $this->service->callAzureFormRecognizer($fileContent, $mimeType);
            // 4. Devolver esa respuesta (parseada o tal cual) al front
            return bodyResponseRequest(EnumResponse::ACCEPTED, $azureResponse);
    
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.sendPassportImage');
        }
    }
    

    
}
