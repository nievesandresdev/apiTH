<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\NotifyUnreadMsg;
use App\Http\Controllers\Controller;
use App\Jobs\Chat\NofityPendingChat;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\NoteGuest;
use App\Models\Query;
use App\Models\RequestSettingsHistory;
use App\Models\Stay;
use App\Models\StayAccess;
use App\Services\ChatService;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Services\Hoster\RequestReviews\RequestReviewsSettingsServices;
use App\Services\Hoster\Stay\StaySettingsServices;
use App\Services\Hoster\Users\UserServices;
use App\Services\QuerySettingsServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class UtilsController extends Controller
{   

    public $settings;
    public $staySettings;
    public $userServices;
    public $chatService;
    public $chatSettingsServices;
    public  $requestReviewsSettingsServices;
    function __construct(
        QuerySettingsServices $_QuerySettingsServices,
        UserServices $userServices,
        ChatService $_ChatService,
        StaySettingsServices $_StaySettingsServices,
        ChatSettingsServices $_ChatSettingsServices,
        RequestReviewsSettingsServices $_RequestReviewsSettingsServices
    )
    {
        $this->settings = $_QuerySettingsServices;
        $this->userServices = $userServices;
        $this->chatService = $_ChatService;
        $this->staySettings = $_StaySettingsServices;
        $this->chatSettingsServices = $_ChatSettingsServices;
        $this->requestReviewsSettingsServices = $_RequestReviewsSettingsServices;
    }

    public function authPusher(Request $request)
    {
        $user = auth()->user(); // O tu lógica de autenticación personalizada
        
        // if ($user) {
            $pusher = new Pusher(
                config('services.pusher.key'), 
                config('services.pusher.secret'), 
                config('services.pusher.id'), 
                [
                    'cluster' => config('services.pusher.cluster'),
                    'useTLS' => true
                ]
            );
    
            $authResponse = $pusher->socket_auth($request->input('channel_name'), $request->input('socket_id'));
            return response($authResponse, 200)->header('Content-Type', 'application/json');
        // } else {
        //     return response('Forbidden', 403);
        // }
    }
    
    public function test()
    {
        $stayId = 443;
        $hotelId = 191;
        $guestData = Guest::find(9);
        $queryInStay =  $guestData->queries()->where('stay_id',$stayId)->where('period','in-stay')->orderBy('created_at','asc')->first();
        $respondedAt = $queryInStay->responded_at;
        $activeWhenResponding = $this->requestReviewsSettingsServices->fieldAtTheMoment('request_to', $respondedAt, $hotelId);
        return $goodAnswers = json_decode($activeWhenResponding);
        $stay = Stay::find($stayId);

        $icon = "Pendiente";
        $answeredTime = null;
        $goodAnswers = ['GOOD','VERYGOOD'];
        if($queryInStay->answered){
            $goodFeedback = $queryInStay ? in_array($queryInStay->qualification, $goodAnswers) : false;
            if($goodFeedback){
                $icon = "Solicitado";
                $answeredTime = $queryInStay->responded_at;
            }else{
                $icon = "No solicitado";
            }
        }
        return [
            "icon" => $icon,
            "answeredTime" => $answeredTime
        ];
    }



}
