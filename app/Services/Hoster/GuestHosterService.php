<?php

namespace App\Services\Hoster;

use App\Mail\Guest\InviteToInWebapp;
use App\Mail\Guest\MsgStay;
use App\Models\Hotel;
use App\Models\StayNotificationSetting;
use App\Services\GuestService;
use App\Services\Hoster\Stay\StaySettingsServices;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;

class GuestHosterService {

    public $mailService;
    public $guestService;
    public $staySettingsServices;

    function __construct(
        MailService $_MailService,
        GuestService $_GuestService,
        StaySettingsServices $_StaySettingsServices
    )
    {
        $this->mailService = $_MailService;
        $this->guestService = $_GuestService;
        $this->staySettingsServices = $_StaySettingsServices;
    }

    public function inviteToHotel($data, $hotelId)
    {
        try {
            // $guest = $this->guestService->saveOrUpdate($data);
            // $settings =  $this->staySettingsServices->getAll($hotelId);
            
            $hotel = Hotel::find($hotelId);
            
            // //prepare msg
            // $link = url('webapp?g='.$guest->id);
            // $link =  includeSubdomainInUrlHuesped($link, $hotel);
            // $msg = $settings->guestcreate_msg_email[$guest->lang_web];
            // $msg = str_replace('[nombre]', $guest->name, $msg);
            // $msg = str_replace('[nombre_del_hotel]', $hotel->name, $msg);
            // $msg = str_replace('[URL]', $link, $msg);
            $this->mailService->sendEmail(new InviteToInWebapp($hotel), "andresdreamerf@gmail.com");
            // return $guest;
        } catch (\Exception $e) {
            return $e;
        }
    }

   



}
