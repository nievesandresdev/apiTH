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
            $context = [
                "dirTemplate" => $request->dirTemplate,
                "languageCodes" => $request->languageCodes,
                "context" => $request->context
            ];
            $data = $this->service->load($context);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.load');
        }
    }

}
