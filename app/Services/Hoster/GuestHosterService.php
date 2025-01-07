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


    public function inviteToHotel($data, $hotel, $chainSubdomain)
    {
        $guest = $this->guestService->saveOrUpdate($data);


        $this->stayServices->guestWelcomeEmail('inviteGuestFromSaas',$chainSubdomain, $hotel, $guest);
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
