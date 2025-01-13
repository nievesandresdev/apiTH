<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

class RevieNotificationController extends Controller
{

    function __construct(
    )
    {
    }


    public function send(Request $request){
        
        try {

            // $data = $request->data; 
            
            $jsonTest = '[{
                "ota": "BOOKING",
                "numbers": 16
            },
            {
                "ota": "GOOGLE",
                "numbers": 5
            },
            {
                "ota": "TRIPADVISOR",
                "numbers": 5
            },
            {
                "ota": "AIRBNB",
                "numbers": 5
            },
            {
                "ota": "AIRBNB",
                "numbers": 5
            },
            {
                "ota": "EXPEDIA",
                "numbers": 7
            }]';
            $data = json_decode($jsonTest, true);

            return $data;

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.send');
        }
    }


}
