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

    function encodeArrayValuesToJsonStrings(array $data): array
    {
        return array_map(fn($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value, $data);
    }


    public function load(LoadTranslateRequest $request){
        
        try {
            $withValidation = isset($request->withValidation) ? $request->withValidation  : true;
            $inputs = $request->context;
            $inputs = $this->encodeArrayValuesToJsonStrings($inputs);
            $context = [
                "dirTemplate" => $request->dirTemplate,
                "languageCodes" => $request->languageCodes,
                "context" => $inputs
            ];

            $responseTranslate = $this->service->translate($context);
            ['errorTranslate' => $errorTranslate, 'input' => $input, 'output' => $output, 'translation' => $translation] = $responseTranslate;
            if ($withValidation && empty($errorTranslate) && !empty($translation)) {
                $responseValidate = $this->service->validate($input, $output);

                ['status' => $status, 'attempts'=>$attempts, 'errorValidate' => $errorValidate] =  $responseValidate;
                if ($status != 200) {
                    \Log::error('ERROR_TRANSLATION', ['status' => $status, 'attempts' => $attempts,  'output' => $output]);
                }
                $data = [
                    'input' => $input,
                    'output' => $output,
                    'translation' => $translation,
                    'errorTranslate' => $errorTranslate,
                    'status' => $status,
                    'attempts' => $attempts,
                    'errorValidate' => $errorValidate,
                ];
                return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
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
            return $responseValidate;
            ['status' => $status, 'attempts'=>$attempts, 'errorValidate' => $errorValidate] =  $responseValidate;
            $data = [
                'output' => $output,
                'status' => $status,
                'attempts' => $attempts,
                'errorValidate' => $errorValidate,
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.validateTranslation');
        }
    }

}