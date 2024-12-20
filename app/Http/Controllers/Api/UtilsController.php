<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\NotifyUnreadMsg;
use App\Http\Controllers\Controller;
use App\Jobs\Chat\NofityPendingChat;
use App\Mail\Guest\MsgStay;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\Hotel;
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
use App\Services\MailService;
use App\Services\QuerySettingsServices;
use App\Services\StayService;
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

    public $querySettingsServices;
    public $staySettings;
    public $userServices;
    public $chatService;
    public $chatSettingsServices;
    public  $requestReviewsSettingsServices;
    public  $utilsHosterServices;
    public  $utilityService;
    public  $mailService;
    public  $stayServices;

    function __construct(
        QuerySettingsServices $_QuerySettingsServices,
        UserServices $userServices,
        ChatService $_ChatService,
        StaySettingsServices $_StaySettingsServices,
        ChatSettingsServices $_ChatSettingsServices,
        RequestReviewsSettingsServices $_RequestReviewsSettingsServices,
        UtilsHosterServices $_UtilsHosterServices,
        UtilityService $_UtilityService,
        MailService $_MailService,
        StayService $_StayService,
    )
    {
        $this->querySettingsServices = $_QuerySettingsServices;
        $this->userServices = $userServices;
        $this->chatService = $_ChatService;
        $this->staySettings = $_StaySettingsServices;
        $this->chatSettingsServices = $_ChatSettingsServices;
        $this->requestReviewsSettingsServices = $_RequestReviewsSettingsServices;
        $this->utilsHosterServices = $_UtilsHosterServices;
        $this->utilityService = $_UtilityService;
        $this->mailService = $_MailService;
        $this->stayServices = $_StayService;
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
        $type = 'welcome';
        $hotel = Hotel::find(191);
        $guest = Guest::find(9);
        $chainSubdomain = $hotel->subdomain;
        $stay = Stay::find(449);

        $checkData = [];
            $queryData = [];
            //stay section

            if($type == 'welcome'){
                if($stay->check_in && $stay->check_out){
                    $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
                    $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
                }
                $webappEditStay = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'editar-estancia/'.$stay->id);
                //

                $checkData = [
                    'title' => "Datos de tu estancia en {$hotel->name}",
                    'formatCheckin' => $formatCheckin,
                    'formatCheckout' => $formatCheckout,
                    'editStayUrl' => $webappEditStay
                ];
            }

        //     //query section
            if($type == 'welcome'){
                $currentPeriod = $this->stayServices->getCurrentPeriod($hotel, $stay);
                $querySettings = $this->querySettingsServices->getAll($hotel->id);
                $hoursAfterCheckin = $this->stayServices->calculateHoursAfterCheckin($hotel, $stay);
                $showQuerySection = true;

                if(
                    $currentPeriod == 'pre-stay' && !$querySettings->pre_stay_activate ||
                    $currentPeriod == 'in-stay' && $hoursAfterCheckin < 24 ||
                    $currentPeriod == 'post-stay'
                ){
                    $showQuerySection = false;
                }
                //
                $webappLinkInbox = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'inbox');
                $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'inbox',"e={$stay->id}&g={$guest->id}&fill=VERYGOOD");

                $queryData = [
                    'showQuerySection' => $showQuerySection,
                    'currentPeriod' => $currentPeriod,
                    'webappLinkInbox' => $webappLinkInbox,
                    'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,

                ];
            }

            $urlWebapp = buildUrlWebApp($chainSubdomain, $hotel->subdomain);

            //
            $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');
            //

            $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);

            //
            // $urlQr = generateQr($hotel->subdomain, $urlWebapp);
            $urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";

            $dataEmail = [
                'checkData' => $checkData,
                'queryData' => $queryData,
                'places' => $crosselling['places'],
                'experiences' => $crosselling['experiences'],
                'facilities' => $crosselling['facilities'],
                'webappChatLink' => $webappChatLink,
                'urlQr' => $urlQr,
                'urlWebapp' => $urlWebapp
            ];

            return view('Mails.guest.msgStay', [
                'type' => $type,
                'hotel' => $hotel,
                'guest' => $guest,
                'data'=> $dataEmail
            ]);
            // $this->mailService->sendEmail(new MsgStay($type, $hotel, $guest, $dataEmail), "andresdreamerf@gmail.com");

    }


    public function test()
    {
        return 'asdasd';
        $hotel = Hotel::find(191);
        $urlWebapp = buildUrlWebApp($hotel->chain->subdomain,$hotel->subdomain);
        // $urlQr = generateQr($hotel->subdomain, $urlWebapp);
        $urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";

        $guest = Guest::find(9);
        $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $hotel->subdomain);
        $this->mailService->sendEmail(new MsgStay(
            'welcome',
            $hotel,
            $urlWebapp,
            $guest,
            $urlQr,
            ['crosselling'=>$crosselling]
        ), 'andresdreamerf@gmail.com');
        return 'hecho'.time();
    }



}
