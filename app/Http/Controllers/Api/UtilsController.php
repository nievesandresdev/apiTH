<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;

/*emails*/
use App\Mail\Guest\{MsgStay, postCheckoutMail,prepareArrival};
use App\Mail\User\RewardsEmail;

/*models*/
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Language;
use App\Models\Query;
use App\Models\QuerySetting;
use App\Models\RequestSetting;
use App\Models\Stay;
use App\Models\RewardStay;
/*services*/
use App\Services\ChatService;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Services\Hoster\RequestReviews\RequestReviewsSettingsServices;
use App\Services\Hoster\Stay\StaySettingsServices;
use App\Services\Hoster\Users\UserServices;
use App\Services\Hoster\UtilsHosterServices;
use App\Services\MailService;
use App\Services\QuerySettingsServices;
use App\Services\RequestSettingService;
use App\Services\StayService;
use App\Services\UtilityService;
use App\Services\UrlOtasService;
use App\Services\Apis\ApiReviewServices;
use App\Services\Hoster\CloneHotelServices;
use App\Services\Hoster\Stay\StayHosterServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
    public  $requestService;
    public  $api_review_service;
    public  $urlOtasService;
    public  $stayHosterServices;
    public $cloneHotelServices;
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
        RequestSettingService $requestService,
        ApiReviewServices $_api_review_service,
        UrlOtasService $urlOtasService,
        StayHosterServices $_StayHosterServices,
        CloneHotelServices $_CloneHotelServices
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
        $this->requestService = $requestService;
        $this->api_review_service = $_api_review_service;
        $this->urlOtasService = $urlOtasService;
        $this->stayHosterServices = $_StayHosterServices;
        $this->cloneHotelServices = $_CloneHotelServices;
    }

    public function testPostCheckin()
    {
        $start = now()->startOfHour()->subDay();
        $end = now()->startOfHour()->subDay()->addHour();

        Log::info('handleSendEmailPostChekin  24h starthour: '.$start.' endhour: '.$end);

        $stays = Stay::with(['guests:id,name,email'])->select(
                'h.checkin','h.id as hotelId','h.chain_id','h.name as hotelName','h.subdomain as hotelSubdomain','h.zone',
                'h.show_facilities','h.show_places','h.show_experiences','h.latitude','h.longitude','h.sender_for_sending_email','h.sender_mail_mask',
                'h.show_checkin_stay','h.city_id',
                'cs.show_guest',
                'stays.check_in','stays.id','stays.check_out',
                'c.subdomain as chainSubdomain'
            )
            ->join('hotels as h', 'stays.hotel_id', '=', 'h.id')
            ->join('chains as c', 'c.id', '=', 'h.chain_id')
            ->join('chat_settings as cs', 'cs.hotel_id', '=', 'h.id')
            ->leftJoin('email_notifications as en', 'stays.id', '=', 'en.stay_id')
            ->leftJoin('hotel_communications as hc', function ($join) {
                $join->on('h.id', '=', 'hc.hotel_id')
                     ->where('hc.type', '=', 'email');
            })
            //aqui se valida si el hotel no tiene checkin entonces se usa 20:00:00 como checkin
            ->whereRaw("
                TIMESTAMP(
                    stays.check_in,
                    IFNULL(NULLIF(h.checkin, ''), '20:00:00')
                ) BETWEEN ? AND ?
            ", [$start, $end])
            ->where('stays.check_out', '>', now()) //solo se envian correos a estancias que no han finalizado
            ->where(function ($query) {
                $query->whereNull('en.post_checkin') // No existe registro
                      ->orWhere('en.post_checkin', 0); // O existe pero no se ha enviado
            })
            ->where(function ($query) {
                $query->whereNull('hc.post_checkin_email') // No existe registro
                      ->orWhere('hc.post_checkin_email', 1); // O existe pero no se ha enviado
            })
            ->get();


        foreach($stays as $stay){
            $hotel = new \stdClass();
            $hotel->checkin = $stay->checkin;
            $hotel->id = $stay->hotelId;
            $hotel->chain_id = $stay->chain_id;
            $hotel->name = $stay->hotelName;
            $hotel->subdomain = $stay->hotelSubdomain;
            $hotel->zone = $stay->zone;
            $hotel->show_facilities = $stay->show_facilities;
            $hotel->show_places = $stay->show_places;
            $hotel->show_experiences = $stay->show_experiences;
            $hotel->latitude = $stay->latitude;
            $hotel->longitude = $stay->longitude;
            $hotel->sender_for_sending_email = $stay->sender_for_sending_email;
            $hotel->sender_mail_mask = $stay->sender_mail_mask;
            $hotel->show_checkin_stay = $stay->show_checkin_stay;
            $hotel->city_id = $stay->city_id;
            $hotel->chatSettings = (object) ['show_guest' => $stay->show_guest];

            foreach ($stay->guests as $guest) {
                Log::info("Enviando correo postCheckin a {$guest->email} (Estancia ID: {$stay->id}, Hotel: {$stay->hotelName})");

                $this->stayServices->guestWelcomeEmail(
                    'postCheckin',
                    $stay->chainSubdomain,
                    $hotel,
                    $guest,
                    $stay
                );
            }

            // Marcar esta estancia como enviada
            DB::table('email_notifications')->insert(
                [
                    'stay_id' => $stay->id,
                    'hotel_id' => $stay->hotelId,
                    'post_checkin' => 1,
                    'sent_at' => now()
                ]
            );

        }

        dd('fin',$stays);
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


    public function testEmailGeneral(){
        $type = 'welcome';
        $hotel = Hotel::find(292);
        //dd($hotel->subdomain);
        $guest = Guest::find(27);
        $chainSubdomain = $hotel->subdomain;
        $stay = Stay::find(81);


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
                    'title' => __('mail.stayCheckDate.title', ['hotel' => $hotel->name]),
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
            $urlCheckin = buildUrlWebApp($chainSubdomain, $hotel->subdomain,"mi-estancia/huespedes/completar-checkin/{$guest->id}");


            //
            // $urlQr = generateQr($hotel->subdomain, $urlWebapp);
             $urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";
             $urlFooterEmail = buildUrlWebApp($chainSubdomain, $hotel->subdomain,"no-notificacion?g={$guest->id}");



            $dataEmail = [
                'checkData' => $checkData,
                'queryData' => $queryData,
                'places' => $crosselling['places'],
                'experiences' => $crosselling['experiences'],
                'facilities' => $crosselling['facilities'],
                'webappChatLink' => $webappChatLink,
                'urlQr' => $urlQr,
                'urlWebapp' => $urlWebapp,
                'urlCheckin' => $urlCheckin,
                'hotel' => $hotel,
                'stay_language' => $stay->language,
                'urlFooterEmail' => $urlFooterEmail
            ];

            //dd($dataEmail);

            //Log::info('guestWelcomeEmail '.json_encode($dataEmail));
            // Log::info('dataEmail '.json_encode($dataEmail));
            // Log::info('hotelid '.json_encode($hotel->id));
            // Log::info('guest '.json_encode($guest));

            //dd($hotel->hotelCommunications);
            //dd($dataEmail);
            $this->mailService->sendEmail(new MsgStay($type, $hotel, $guest, $dataEmail,false,true), 'francisco20990@gmail.com');


            return view('Mails.guest.msgStay', [
                'type' => $type,
                'hotel' => $hotel,
                'guest' => $guest,
                'data'=> $dataEmail,
                'after' => false,
                'beforeCheckin' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error service guestWelcomeEmail: ' . $e->getMessage());
            DB::rollback();
            return $e;
        }
    }


    public function test()
    {
        
        $codeDiff = Carbon::now()->timestamp;
        $stringDiff = 'B';
        $originalHotel = $this->cloneHotelServices->findOriginalHotel();
        if(!$originalHotel) return 'No existe el Hotel';
        $copyChain = $this->cloneHotelServices->CreateChainToCopyHotel($originalHotel, $stringDiff);
        $copyHotel = $this->cloneHotelServices->CreateCopyHotel($originalHotel, $stringDiff, $copyChain);
        $syncTranslateCopyHotel = $this->cloneHotelServices->SyncTranslateCopyHotel($originalHotel, $copyHotel);
        return $syncTranslateCopyHotel;
    }

    public function testEmailPostCheckout(){
        $type = 'post-checkout';
        $hotel = Hotel::find(292);
        //$guest = Guest::find(146);
        $guest = Guest::find(49);
        $chainSubdomain = $hotel->subdomain;
        //$stay = Stay::find(630);
        $stay = Stay::with('queries')->where('id',81)->first();



        try {

           //query section
            $currentPeriod = $this->stayServices->getCurrentPeriod($hotel, $stay);
            $querySettings = $this->querySettingsServices->getAll($hotel->id);
            $hoursAfterCheckin = $this->stayServices->calculateHoursAfterCheckin($hotel, $stay);
            $showQuerySection = true;
            $answered = Query::where('stay_id',$stay->id)->where('period','post-stay')->where('guest_id',$guest->id)->first();
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
                'answered' => true
            ];

            $urlWebapp = buildUrlWebApp($chainSubdomain, $hotel->subdomain);
            $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');
            $reservationURl = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'reservar-estancia');
            $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);
            $settingEnabled = $this->requestService->getAll($hotel->id);
            $otasWithUrls = $this->urlOtasService->getOtasWithUrls($hotel, $settingEnabled->otas_enabled);


            //
            // $urlQr = generateQr($hotel->subdomain, $urlWebapp);
            $urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";

            $dataEmail = [
                'queryData' => $queryData,
                'places' => $crosselling['places'],
                'webappChatLink' => $webappChatLink,
                'urlQr' => $urlQr,
                'urlWebapp' => $urlWebapp,
                'otas' => $otasWithUrls,
                'reservationURl' => $reservationURl
            ];

            //dd($dataEmail);
            $this->mailService->sendEmail(new postCheckoutMail($type, $hotel, $guest, $dataEmail,true), 'francisco20990@gmail.com');

            return view('Mails.guest.postCheckoutEmail', [
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

    public function testPrepareYourArrival(){
        $type = 'prepare-arrival';
        $hotel = Hotel::find(291);
        //$guest = Guest::find(146);
        $guest = Guest::find(22);
        $chainSubdomain = $hotel->subdomain;
        //$stay = Stay::find(630);
        $stay = Stay::with('queries')->where('id',82)->first();



        try {
            $checkData = [];
            $queryData = [];
            //stay section
            /* if($type == 'welcome'){ */
                if($stay->check_in && $stay->check_out){
                    $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
                    $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
                }
                $webappEditStay = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'editar-estancia/'.$stay->id);

                //

                $checkData = [
                    'title' => __('mail.stayCheckDate.title2', ['hotel' => $hotel->name]),
                    'formatCheckin' => $formatCheckin,
                    'formatCheckout' => $formatCheckout,
                    'editStayUrl' => $webappEditStay,
                ];
           /*  } */


        //     //query section
                $currentPeriod = $this->stayServices->getCurrentPeriod($hotel, $stay);
                $querySettings = $this->querySettingsServices->getAll($hotel->id);
                $hoursAfterCheckin = $this->stayServices->calculateHoursAfterCheckin($hotel, $stay);
                $answered = Query::where('stay_id',$stay->id)->where('guest_id',$guest->id)->first();

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
                    'answered' => $answered->answered == 1 ? true : false

                ];

            $urlWebapp = buildUrlWebApp($chainSubdomain, $hotel->subdomain);


            //
            $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');
            $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);
            $urlCheckin = buildUrlWebApp($chainSubdomain, $hotel->subdomain,"mi-estancia/huespedes/completar-checkin/{$guest->id}");



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
                'urlWebapp' => $urlWebapp,
                'urlCheckin' => $urlCheckin,
            ];

            //dd($dataEmail,$hotel);

            $communication = $hotel->hotelCommunications->firstWhere('type', 'email');
            $shouldSend = !$communication || $communication->pre_checkin_email;

            if($shouldSend){
                $this->mailService->sendEmail(new prepareArrival($type, $hotel, $guest, $dataEmail,true), 'francisco20990@gmail.com');
            }else{
                dd('no se envia');
            }


            return view('Mails.guest.prepareYourArrival', [
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

    public function testEmailReferent(){



        $rewardStay = RewardStay::with(['reward','guest','stay','hotel'])->find(1);
        $chatSettings = $this->chatSettingsServices->getAll($rewardStay->hotel->id);

        //dd($chatSettings->show_guest);
        //dd($rewardStay);

        try {



            //
            $webappChatLink = buildUrlWebApp($rewardStay->hotel->subdomain, $rewardStay->hotel->subdomain,'chat');
            //$urlQr = generateQr($rewardStay->hotel->subdomain, $urlWebapp);



            //
            // $urlQr = generateQr($hotel->subdomain, $urlWebapp);
             $urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";




            $dataEmail = [
                'webappChatLink' => $webappChatLink,
                'urlQr' => $urlQr,
            ];

            //dd($dataEmail,$rewardStay->hotel->chatSettings->show_guest);


            $this->mailService->sendEmail(new RewardsEmail($rewardStay->hotel, $rewardStay, $dataEmail), 'francisco20990@gmail.com');


            return view('Mails.users.rewards', [
                'hotel' => $rewardStay->hotel,
                'rewardStay' => $rewardStay,
                'data'=> $dataEmail,
            ]);

        } catch (\Exception $e) {
            Log::error('Error service guestWelcomeEmail: ' . $e->getMessage());
            DB::rollback();
            return $e;
        }
    }








}
