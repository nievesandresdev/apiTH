<?php

namespace App\Services\Hoster;

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
            $guest = $this->guestService->saveOrUpdate($data);
            $settings =  $this->staySettingsServices->getAll($hotelId);
            
            $hotel = Hotel::find($hotelId);
            $data = [
                'guest_id' => $guest->id,
                'stay_lang' => $guest->lang_web,
                'msg_text' => $settings->guestcreate_msg_email[$guest->lang_web],
                'guest_name' => $guest->name,
                'hotel_name' => $hotel->name,
                'hotel_id' => $hotel->id,
            ];
            $msg = prepareMessage($data,$hotel);
            $link = prepareLink($data,$hotel);
            $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link), $guest->email);
            return $guest;
        } catch (\Exception $e) {
            return $e;
        }
    }

   



}
