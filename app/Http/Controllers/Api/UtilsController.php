<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\NotifyUnreadMsg;
use App\Http\Controllers\Controller;
use App\Jobs\Chat\NofityPendingChat;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\hotel;
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
use App\Services\Hoster\UtilsHosterServices;
use App\Services\QuerySettingsServices;
use App\Services\UtilityService;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class UtilsController extends Controller
{   

    public $settings;
    public $staySettings;
    public $userServices;
    public $chatService;
    public $chatSettingsServices;
    public  $requestReviewsSettingsServices;
    public  $utilsHosterServices;
    public  $utilityService;

    function __construct(
        QuerySettingsServices $_QuerySettingsServices,
        UserServices $userServices,
        ChatService $_ChatService,
        StaySettingsServices $_StaySettingsServices,
        ChatSettingsServices $_ChatSettingsServices,
        RequestReviewsSettingsServices $_RequestReviewsSettingsServices,
        UtilsHosterServices $_UtilsHosterServices,
        UtilityService $_UtilityService
    )
    {
        $this->settings = $_QuerySettingsServices;
        $this->userServices = $userServices;
        $this->chatService = $_ChatService;
        $this->staySettings = $_StaySettingsServices;
        $this->chatSettingsServices = $_ChatSettingsServices;
        $this->requestReviewsSettingsServices = $_RequestReviewsSettingsServices;
        $this->utilsHosterServices = $_UtilsHosterServices;
        $this->utilityService = $_UtilityService;
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

    public function testTemplateEmail()
    {
        
        $hotel = hotel::find(191);
        $stay = Stay::find(460);
        $chainSubdomain = $hotel->subdomain;

        $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
        $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
        $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);

        $webappLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain);
        $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');

        return view('Mails.guest.InviteToInWebapp', [
            'hotel' => $hotel,
            'formatCheckin' => $formatCheckin,
            'formatCheckout' => $formatCheckout,
            'crosselling' => $crosselling,
            'webappLink' => $webappLink,
            'webappChatLink' => $webappChatLink,
        ]);
    }

    
    public function test()
    {
        
        $qr = QrCode::format('png')->size(300)->generate('teteo');
        // Definir el nombre del archivo con una marca de tiempo única
        $nombreArchivo = 'qr_testeo.png';

        // Definir la ruta completa donde se guardará el QR en S3
        $rutaArchivo = 'qrcodes/' . $nombreArchivo;

        if (Storage::disk('s3')->exists($rutaArchivo)) {
            Storage::disk('s3')->delete($rutaArchivo);
        }

        $storage = Storage::disk('s3')->put($rutaArchivo, $qr, 'public');

        // Obtener la URL pública del archivo guardado
        return $urlQr = Storage::disk('s3')->url($rutaArchivo);
    }



}
