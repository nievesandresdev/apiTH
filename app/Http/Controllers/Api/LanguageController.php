<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use App\Services\LanguageServices;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

use App\Models\Language;

class LanguageController extends Controller
{
    public $service;

    function __construct(
        // LanguageServices $_LanguageServices,
    )
    {
        // $this->service = $_LanguageServices;
    }

    

    public function getAll(Request $request){
        
        try {
            $isWebapp = isset($request->isWebapp) ? intval($request->isWebapp) : true;
            if ($isWebapp) {
                $data = ['es', 'en', 'fr'];
                return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
            }
            
            $languages = Language::all();

            return bodyResponseRequest(EnumResponse::ACCEPTED, $languages);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }


    

}
