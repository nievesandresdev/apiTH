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
use App\Models\Stay;
use App\Models\StayAccess;
use App\Services\ChatService;
use App\Services\Hoster\Chat\ChatSettingsServices;
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
    function __construct(
        QuerySettingsServices $_QuerySettingsServices,
        UserServices $userServices,
        ChatService $_ChatService,
        StaySettingsServices $_StaySettingsServices,
        ChatSettingsServices $_ChatSettingsServices
    )
    {
        $this->settings = $_QuerySettingsServices;
        $this->userServices = $userServices;
        $this->chatService = $_ChatService;
        $this->staySettings = $_StaySettingsServices;
        $this->chatSettingsServices = $_ChatSettingsServices;
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
        return buildUrlWebApp('acendas');
    }



}
