<?php

namespace App\Http\Controllers\Api\Chatgpt;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

use App\Services\ChatService;
use App\Services\Chatgpt\TranslateService;

use App\Http\Requests\LoadTranslateRequest;
use App\Http\Requests\ValidateTranslateRequest;

class TranslateController extends Controller
{
    public $service;

    function __construct(
        TranslateService $_TranslateService
    )
    {
        $this->service = $_TranslateService;
    }


    public function load(LoadTranslateRequest $request){
        
        try {

            $withValidation = isset($request->withValidation) ? $request->withValidation  : true;
            $context = [
                "dirTemplate" => $request->dirTemplate,
                "languageCodes" => $request->languageCodes,
                "context" => $request->context
            ];
            $responseTranslate = $this->service->translate($context);
            ['errorTranslate' => $errorTranslate, 'input' => $input, 'output' => $output, 'translation' => $translation] = $responseTranslate;
            if ($withValidation) {
                $responseValidate = $this->service->validate($input, $output);
                ['status' => $status, 'attempts'=>$attempts, 'errorValidate' => $errorValidate] =  $responseValidate;
                $data = [
                    'input' => $input,
                    'output' => $output,
                    'translation' => $translation,
                    'errorTranslate' => $errorTranslate,
                    'status' => $status,
                    'attempts' => $attempts,
                    'errorValidate' => $errorValidate,
                ];
            }
            $data = [
                'input' => $input,
                'output' => $output,
                'translation' => $translation,
                'errorTranslate' => $errorTranslate,
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.load');
        }
    }

    public function validateTranslation (ValidateTranslateRequest $request) {
        try {
            $input = $request->input ?? nulll;
            $output = $request->output ?? nulll;
            $responseValidate = $this->service->validate($input, $output);
            ['status' => $status, 'attempts'=>$attempts, 'errorValidate' => $errorValidate] =  $responseValidate;
            $data = [
                'output' => $output,
                'status' => $status,
                'attempts' => $attempts,
                'errorValidate' => $errorValidate,
            ];
            return $data;
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.validateTranslation');
        }
    }

}
