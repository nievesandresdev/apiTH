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
use stdClass;

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


    public function test(){
        $type = 'checkout';
        $hotel = Hotel::find(240);
        $guest = Guest::find(9);
        $chainSubdomain = $hotel->subdomain;
        $stay = Stay::find(565);


        try {
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
            if($type == 'welcome' || $type == 'postCheckin'){
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

            //dd($dataEmail);

            //Log::info('guestWelcomeEmail '.json_encode($dataEmail));
            // Log::info('dataEmail '.json_encode($dataEmail));
            // Log::info('hotelid '.json_encode($hotel->id));
            // Log::info('guest '.json_encode($guest));

            $this->mailService->sendEmail(new MsgStay($type, $hotel, $guest, $dataEmail,true), 'francisco20990@gmail.com');


            return view('Mails.guest.msgStay', [
                'type' => $type,
                'hotel' => $hotel,
                'guest' => $guest,
                'data'=> $dataEmail,
                'after' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error service guestWelcomeEmail: ' . $e->getMessage());
            DB::rollback();
            return $e;
        }
    }


    public function test2()
    {
        // Ayer a esta misma hora (24h antes)
        $start = now()->subDay();
        // Ayer a esta misma hora + 1 hora
        $end   = now()->subDay()->addHour();


        Log::info('comienza consulta estancias hace 24h starthour: '.$start.' endhour: '.$end);

        return $stays = Stay::with(['guests:id,name,email'])->select(
                'h.checkin','h.id as hotelId','h.chain_id','h.name as hotelName','h.subdomain as hotelSubdomain','h.zone',
                'h.show_facilities','h.show_places','h.show_experiences','h.latitude','h.longitude','h.sender_for_sending_email','h.sender_mail_mask',
                //
                'stays.check_in','stays.id','stays.check_out',
                'c.subdomain as chainSubdomain',
            )
            ->join('hotels as h', 'stays.hotel_id', '=', 'h.id')
            ->join('chains as c', 'c.id', '=', 'h.chain_id')
            ->whereRaw("
                TIMESTAMP(
                    stays.check_in,
                    COALESCE(NULLIF(h.checkin, ''), '14:00:00')
                ) BETWEEN ? AND ?
            ", [
                $start,
                $end
            ])
            ->get();

        // foreach($stays as $stay){
        //     //create hotel object
        //     $hotel = new stdClass();
        //     $hotel->checkin = $stay->checkin;
        //     $hotel->id = $stay->hotelId;
        //     $hotel->chain_id = $stay->chain_id;
        //     $hotel->name = $stay->hotelName;
        //     $hotel->subdomain = $stay->hotelSubdomain;
        //     $hotel->zone = $stay->zone;
        //     $hotel->show_facilities = $stay->show_facilities;
        //     $hotel->show_places = $stay->show_places;
        //     $hotel->show_experiences = $stay->show_experiences;
        //     $hotel->latitude = $stay->latitude;
        //     $hotel->longitude = $stay->longitude;
        //     $hotel->sender_for_sending_email = $stay->sender_for_sending_email;
        //     $hotel->sender_mail_mask = $stay->sender_mail_mask;

        //     foreach ($stay->guests as $guest) {
        //         $this->stayServices->guestWelcomeEmail('postCheckin', $stay->chainSubdomain, $hotel, $guest, $stay);
        //     }
        // }

        // return 'listo';
    }






}
