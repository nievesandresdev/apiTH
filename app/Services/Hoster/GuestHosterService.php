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

    function __construct(
        MailService $_MailService,
        GuestService $_GuestService,
        StaySettingsServices $_StaySettingsServices,
        UtilsHosterServices $_UtilsHosterServices,
        UtilityService $_UtilityService
    )
    {
        $this->mailService = $_MailService;
        $this->guestService = $_GuestService;
        $this->staySettingsServices = $_StaySettingsServices;
        $this->utilsHosterServices = $_UtilsHosterServices;
        $this->utilityService = $_UtilityService;
    }


    public function inviteToHotel($data, $hotelId)
    {
        try {

            $hotel = Hotel::find($hotelId);        
            Log::info('hotel '. json_encode($hotel));
            $urlWebapp = buildUrlWebApp($hotel->chain->subdomain,$hotel->subdomain);
            Log::info('urlWebapp '. json_encode($urlWebapp));

            $qr = QrCode::format('png')->size(300)->generate($urlWebapp);
            // Definir el nombre del archivo con una marca de tiempo única
            $nombreArchivo = 'qr_' . $hotel->subdomain . '.png';

            // Definir la ruta completa donde se guardará el QR en S3
            $rutaArchivo = 'qrcodes/' . $nombreArchivo;

            if (Storage::disk('s3')->exists($rutaArchivo)) {
                Storage::disk('s3')->delete($rutaArchivo);
            }

            $storage = Storage::disk('s3')->put($rutaArchivo, $qr, 'public');

            // Obtener la URL pública del archivo guardado
            $urlQr = Storage::disk('s3')->url($rutaArchivo);
            Log::info('urlQr '. json_encode($urlQr));
        
            
            //     $urlQr = generateQr($hotel->subdomain, $urlWebapp);
            //     Log::info('urlQr '. json_encode($urlQr));
            //     $guest = Guest::find(9);
            //     Log::info('guest '. json_encode($guest));
            //     $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $hotel->subdomain);
            //     Log::info('crosselling '. json_encode($crosselling));

            //     $this->mailService->sendEmail(new MsgStay(
            //         false, 
            //         $hotel, 
            //         $urlWebapp, 
            //         false, 
            //         $guest->name, 
            //         false,
            //         $urlQr,
            //         true,
            //         $crosselling
            // ), 'andresdreamerf@gmail.com');
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
