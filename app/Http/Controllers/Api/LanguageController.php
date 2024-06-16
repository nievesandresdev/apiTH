<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use App\Services\LanguageServices;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

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
            $data = getAllLanguages();
            return $data;
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    

}
