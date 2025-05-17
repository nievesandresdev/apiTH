<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Mail\Guest\MsgStay;
use App\Mail\Guest\prepareArrival;
use App\Services\MailService;
use App\Services\UtilityService;
use App\Services\Hoster\UtilsHosterServices;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class EmailTestController extends Controller
{
    protected $mailService;
    protected $utilityService;
    protected $utilsHosterServices;

    public function __construct(
        MailService $mailService,
        UtilityService $utilityService,
        UtilsHosterServices $utilsHosterServices
    )
    {
        $this->mailService = $mailService;
        $this->utilityService = $utilityService;
        $this->utilsHosterServices = $utilsHosterServices;
    }

    public function sendEmails(Request $request){
        /* $request->name email etc...
        {
                "email": "francisco20990@gmail.com",
                "name": "Fran",
                "date": "08/05/2025 - 10/05/2025",
                "idioma": "ca",
                "emails": [
                    "welcome"
                ],
                hotel : { .... todo lo del hotel}
            }
        */
        App::setLocale($request->idioma);
        try {
            $hotel = (object)$request->hotel;
            if (isset($hotel->chat_settings)) {
                $hotel->chatSettings = (object)$hotel->chat_settings;
            }

            $guest = (object)[
                'email' => $request->email,
                'name' => $request->name,
                'lang_web' => $request->idioma,
                'off_email' => false
            ];

            // Send emails based on type
            foreach ($request->emails as $emailType) {
                switch ($emailType) {
                    case 'welcome':
                        $this->sendWelcomeEmail($hotel, $guest, $request);
                        break;
                    case 'prepareArrival':
                        $this->sendPrepareArrivalEmail($hotel, $guest, $request);
                        break;
                }
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, ['request' => $request->all()], 'sendEmails');
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), ['error' => $e->getMessage()], 'sendEmails');
        }
    }

    protected function sendWelcomeEmail($hotel, $guest, $request)
    {
        // Format dates
        $checkinDate = Carbon::parse($request->date_guest['start']);
        $checkoutDate = Carbon::parse($request->date_guest['end']);

        $formatCheckin = [
            'dayDate' => $checkinDate->format('d'),
            'weekDay' => $checkinDate->format('d'),
            'month' => $checkinDate->format('M')
        ];

        $formatCheckout = [
            'dayDate' => $checkoutDate->format('d'),
            'weekDay' => $checkoutDate->format('d'),
            'month' => $checkoutDate->format('M')
        ];

        $checkData = [
            'title' => __('mail.stayCheckDate.title', ['hotel' => $hotel->name]),
            'formatCheckin' => $formatCheckin,
            'formatCheckout' => $formatCheckout,
            'editStayUrl' => '#'
        ];

        $queryData = [
            'showQuerySection' => true,
            'currentPeriod' => 'in-stay',
            'webappLinkInbox' => '#',
            'webappLinkInboxGoodFeel' => '#'
        ];

        $dataEmail = [
            'checkData' => $checkData,
            'queryData' => $queryData,
            'places' => [],
            'experiences' => [],
            'facilities' => [],
            'webappChatLink' => '#',
            'urlQr' => "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png",
            'urlWebapp' => '#',
            'urlCheckin' => '#',
            'guest_language' => $guest->lang_web,
            'urlFooterEmail' => '#',
            'urlPrivacy' => '#'
        ];

        $this->mailService->sendEmail(
            new MsgStay('welcome', $hotel, $guest, $dataEmail, false, false),
            $guest->email
        );
    }

    protected function sendPrepareArrivalEmail($hotel, $guest, $request)
    {
        // Format dates
        $checkinDate = Carbon::parse($request->date_guest['start']);
        $checkoutDate = Carbon::parse($request->date_guest['end']);

        $formatCheckin = [
            'dayDate' => $checkinDate->format('d'),
            'weekDay' => $checkinDate->format('d'),
            'month' => $checkinDate->format('M')
        ];

        $formatCheckout = [
            'dayDate' => $checkoutDate->format('d'),
            'weekDay' => $checkoutDate->format('d'),
            'month' => $checkoutDate->format('M')
        ];

        $checkData = [
            'title' => __('mail.stayCheckDate.title', ['hotel' => $hotel->name]),
            'formatCheckin' => $formatCheckin,
            'formatCheckout' => $formatCheckout,
            'editStayUrl' => '#'
        ];

        $queryData = [
            'currentPeriod' => 'pre-stay',
            'webappLinkInbox' => '#',
            'webappLinkInboxGoodFeel' => '#',
            'answered' => false // Agregamos esta propiedad que faltaba
        ];

        $dataEmail = [
            'checkData' => $checkData,
            'queryData' => $queryData,
            'places' => [],
            'experiences' => [],
            'facilities' => [],
            'webappChatLink' => '#',
            'urlQr' => "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png",
            'urlWebapp' => '#',
            'urlCheckin' => '#',
            'guest_language' => $guest->lang_web,
            'urlFooterEmail' => '#',
            'urlPrivacy' => '#'
        ];

        $this->mailService->sendEmail(
            new prepareArrival('prepare-arrival', $hotel, $guest, $dataEmail, true),
            $guest->email
        );
    }
}
