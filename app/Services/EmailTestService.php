<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Services\UtilityService;
use App\Services\Hoster\UtilsHosterServices;

class EmailTestService
{
    protected $utilityService;
    protected $utilsHosterServices;

    public function __construct(
        UtilityService $utilityService,
        UtilsHosterServices $utilsHosterServices
    )
    {
        $this->utilityService = $utilityService;
        $this->utilsHosterServices = $utilsHosterServices;
    }

    public function prepareWelcomeEmailData($hotel, $guest, $request)
    {
        $dates = $this->formatDates($request->date_guest);

        return [
            'checkData' => $this->prepareCheckData($hotel, $dates),
            'queryData' => [
                'showQuerySection' => true,
                'currentPeriod' => 'in-stay',
                'webappLinkInbox' => '#',
                'webappLinkInboxGoodFeel' => '#'
            ],
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
    }

    public function preparePrepareArrivalEmailData($hotel, $guest, $request)
    {
        $dates = $this->formatDates($request->date_guest);
        $chainSubdomain = $hotel->subdomain;
        $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);

        return [
            'checkData' => $this->prepareCheckData($hotel, $dates),
            'queryData' => [
                'currentPeriod' => 'pre-stay',
                'webappLinkInbox' => '#',
                'webappLinkInboxGoodFeel' => '#',
                'answered' => false
            ],
            'places' => $crosselling['places'],
            'experiences' => $crosselling['experiences'],
            'facilities' => $crosselling['facilities'],
            'webappChatLink' => '#',
            'urlQr' => "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png",
            'urlWebapp' => '#',
            'urlCheckin' => '#',
            'guest_language' => $guest->lang_web,
            'urlFooterEmail' => '#',
            'urlPrivacy' => '#'
        ];
    }

    public function preparePostCheckinEmailData($hotel, $guest, $request)
    {
        $dates = $this->formatDates($request->date_guest);

        return [
            'checkData' => $this->prepareCheckData($hotel, $dates),
            'queryData' => [
                'currentPeriod' => 'in-stay',
                'webappLinkInbox' => '#',
                'webappLinkInboxGoodFeel' => '#',
                'showQuerySection' => true
            ],
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
    }

    public function prepareCheckoutEmailData($hotel, $guest, $request)
    {
        $dates = $this->formatDates($request->date_guest);

        return [
            'checkData' => $this->prepareCheckData($hotel, $dates),
            'queryData' => [
                'currentPeriod' => 'post-stay',
                'webappLinkInbox' => '#',
                'webappLinkInboxGoodFeel' => '#',
                'showQuerySection' => true
            ],
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
    }

    public function preparePostCheckoutEmailData($hotel, $guest, $request)
    {
        $dates = $this->formatDates($request->date_guest);

        return [
            'queryData' => [
                'currentPeriod' => 'post-stay',
                'webappLinkInbox' => '#',
                'webappLinkInboxGoodFeel' => '#',
                'answered' => false
            ],
            'places' => [],
            'experiences' => [],
            'facilities' => [],
            'urlQr' => "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png",
            'urlWebapp' => '#',
            'reservationURl' => '#',
            'urlPrivacy' => '#',
            'urlFooterEmail' => '#'
        ];
    }

    protected function formatDates($dateGuest)
    {
        $checkinDate = Carbon::parse($dateGuest['start']);
        $checkoutDate = Carbon::parse($dateGuest['end']);

        return [
            'checkin' => [
                'dayDate' => $checkinDate->format('d'),
                'weekDay' => $checkinDate->format('d'),
                'month' => $checkinDate->format('M')
            ],
            'checkout' => [
                'dayDate' => $checkoutDate->format('d'),
                'weekDay' => $checkoutDate->format('d'),
                'month' => $checkoutDate->format('M')
            ]
        ];
    }

    protected function prepareCheckData($hotel, $dates)
    {
        return [
            'title' => __('mail.stayCheckDate.title', ['hotel' => $hotel->name]),
            'formatCheckin' => $dates['checkin'],
            'formatCheckout' => $dates['checkout'],
            'editStayUrl' => '#'
        ];
    }
}
