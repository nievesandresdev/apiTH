<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\NotifyUnreadMsg;
use App\Http\Controllers\Controller;
use App\Jobs\Chat\NofityPendingChat;
use App\Models\ChatMessage;
use App\Models\Query;
use App\Models\Stay;
use App\Services\ChatService;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Services\Hoster\Users\UserServices;
use App\Services\QuerySettingsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Pusher\Pusher;

class UtilsController extends Controller
{   

    public $settings;
    public $userServices;
    public $chatService;

    function __construct(
        QuerySettingsServices $_QuerySettingsServices,
        UserServices $userServices,
        ChatService $_ChatService
    )
    {
        $this->settings = $_QuerySettingsServices;
        $this->userServices = $userServices;
        $this->chatService = $_ChatService;
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
        DB::table('jobs')->where('payload', 'like', '%send-by9%')->delete();
        $settings = $this->settings->notifications(191);
        /**
         * trae los ususarios y sus roles asociados al hotel en cuestion
         */
        $queryUsers = $this->userServices->getUsersHotelBasicData(191);

        // Extraer los roles de usuario a notificar para un nuevo mensaje
        $rolesToNotifyNewFeddback = collect($settings->email_notify_new_feedback_to);
        $getUsersRoleNewFeedback = $queryUsers->filter(function ($user) use ($rolesToNotifyNewFeddback) {
            return $rolesToNotifyNewFeddback->contains($user['role']);
        });
        // Extraer los roles de usuario a notificar para chat pendiente luego de 10 min
        $rolesToNotifyPendingFeedback = collect($settings->email_notify_pending_feedback_to);
        $getUsersRolePendingFeedback = $queryUsers->filter(function ($user) use ($rolesToNotifyPendingFeedback) {
            return $rolesToNotifyPendingFeedback->contains($user['role']);
        });

    }


}
