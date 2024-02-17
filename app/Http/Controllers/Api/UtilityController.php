<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\hotel;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\UtilityService;
use App\Services\ExperienceService;
use App\Services\PlaceService;

// use App\Http\Resources\AutocompleteResource;

use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UtilityController extends Controller
{
    function __construct(
        UtilityService $_UtilityService,
        ExperienceService $_ExperienceService,
        PlaceService $_PlaceService
    )
    {
        $this->service = $_UtilityService;
        $this->experienceService = $_ExperienceService;
        $this->placeService = $_PlaceService;
    }

    public function getExpAndPlace (Request $request) {
        try {
            $modelHotel = $request->attributes->get('hotel');
            $typeSearch = $request->typeSearch;
            $typePlace = $request->typePlace;
            $categoryPlace = $request->categoryPlace;
            $search = $request->search ?? null;
            $city = $modelHotel->zone;

            $data = collect([]);
            $totalLength = 8;
            if($typeSearch == 'place'){
                $responseService = $this->placeService->getPlacesBySearch ($modelHotel, $search, $totalLength, $typePlace, $categoryPlace);
            }else{
                $responseService = $this->experienceService->getExperiencesBySearch($modelHotel, $search, $totalLength);
            };
            return bodyResponseRequest(EnumResponse::ACCEPTED, $responseService);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getExpAndPlace');
        }
    }


    public function getPhoneCodesApi(Request $request)
    {
        
        $data = json_decode(Storage::disk('local')->get('phone-codes.json'), true);
        if($data){
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        }
        $data = [
            'message' => __('response.bad_request_long')
        ];
        return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
        
    }

    public function test()
    {
        return $message = ChatMessage::with('chat')->find(68);
        Log::info('Automatic MSG '.$message->chat->pending);
        if($message->chat->pending){
            $chatMessage = new ChatMessage([
                'chat_id' => null,
                'text' => null,
                'status' => 'Entregado',
                'by' => 'Hoster',
                'automatic' => true
            ]);

            // $hotel = hotel::find(191);
            // $msg = $hotel->chatMessages()->save($chatMessage);
            // $msg->load('messageable');
            // sendEventPusher('private-update-chat.' . $this->stay_id, 'App\Events\UpdateChatEvent', ['message' => $msg]);
        }
    }


}