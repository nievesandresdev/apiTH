<?php

namespace App\Services\Hoster;

use App\Mail\Guest\InviteToInWebapp;
use App\Mail\Guest\MsgStay;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\StayNotificationSetting;
use App\Services\GuestService;
use App\Services\Hoster\Stay\StaySettingsServices;
use App\Services\MailService;
use App\Services\QuerySettingsServices;
use App\Services\StayService;
use App\Services\UtilityService;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class GuestHosterService {

    public $mailService;
    public $guestService;
    public $staySettingsServices;
    public $utilsHosterServices;
    public $utilityService;
    public $stayServices;
    public $querySettingsServices;

    function __construct(
        MailService $_MailService,
        GuestService $_GuestService,
        StaySettingsServices $_StaySettingsServices,
        UtilsHosterServices $_UtilsHosterServices,
        UtilityService $_UtilityService,
        StayService $_StayService,
        QuerySettingsServices $_QuerySettingsServices
    )
    {
        $this->mailService = $_MailService;
        $this->guestService = $_GuestService;
        $this->staySettingsServices = $_StaySettingsServices;
        $this->utilsHosterServices = $_UtilsHosterServices;
        $this->utilityService = $_UtilityService;
        $this->stayServices = $_StayService;
        $this->querySettingsServices = $_QuerySettingsServices;
    }


    public function inviteToHotel($data, $hotelId)
    {
        try {
            // $urlWebapp = buildUrlWebApp($hotel->chain->subdomain,$hotel->subdomain);
            // return $urlQr = generateQr($hotel->subdomain, $urlWebapp);

            $hotel = Hotel::find($hotelId);
            $guest = Guest::find(9);
            $stay =  $this->stayServices->findbyId(460);

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
            
            $chainSubdomain = $hotel->subdomain;
    
            $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
            $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
            
    
            // $webappLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain);
            $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');
            $webappEditStay = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'editar-estancia/'.$stay->id);
            //        
            $webappLinkInbox = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'inbox');
            $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'inbox',"e={$stay->id}&g={$guest->id}&fill=VERYGOOD");
            
            $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);

            $this->mailService->sendEmail(new MsgStay(
                'welcome',
                $hotel,
                $guest,
                [
                    'checkData' => [
                        'title' => "Datos de tu estancia en {$hotel->name}",
                        'formatCheckin' => $formatCheckin,
                        'formatCheckout' => $formatCheckout,
                        'editStayUrl' => $webappEditStay
                    ],
                    'queryData' => [
                        'showQuerySection' => $showQuerySection,
                        'currentPeriod' => $currentPeriod,
                        'webappLinkInbox' => $webappLinkInbox,
                        'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,
                    ],
                    'places' => $crosselling['places'],
                    'experiences' => $crosselling['experiences'],
                    'facilities' => $crosselling['facilities'],
                ]  
            ), 'andresdreamerf@gmail.com');
        } catch (\Exception $e) {
            return $e;
        }
    }

    // public function inviteToHotel($data, $hotelId)
    // {
    //     try {
    //         // $guest = $this->guestService->saveOrUpdate($data);
    //         // $settings =  $this->staySettingsServices->getAll($hotelId);
            
    //         $hotel = Hotel::find($hotelId);

    //         // //prepare msg
    //         // $link = url('webapp?g='.$guest->id);
    //         // $link =  includeSubdomainInUrlHuesped($link, $hotel);
    //         // $msg = $settings->guestcreate_msg_email[$guest->lang_web];
    //         // $msg = str_replace('[nombre]', $guest->name, $msg);
    //         // $msg = str_replace('[nombre_del_hotel]', $hotel->name, $msg);
    //         // $msg = str_replace('[URL]', $link, $msg);

    //         $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $hotel->subdomain);

    //         $this->mailService->sendEmail(new InviteToInWebapp($hotel,$crosselling), "andresdreamerf@gmail.com");
    //         // return $guest;
    //     } catch (\Exception $e) {
    //         return $e;
    //     }
    // }

   



}
