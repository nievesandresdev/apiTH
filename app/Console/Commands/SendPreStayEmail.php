<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\Queries\InsistencePostStayResponse;
use App\Mail\Queries\RequestReviewGuest;
use App\Models\Stay;
use App\Services\RequestSettingService;
use App\Services\StayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\MailService;
use App\Mail\Guest\MsgStay;
use App\Mail\Guest\postCheckoutMail;
use App\Services\QuerySettingsServices;
use App\Mail\Guest\{prepareArrival};
use App\Services\UtilityService;
use App\Services\Hoster\UtilsHosterServices;
use stdClass;

class SendPreStayEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-pre-stay-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'correos que se envian previos en pre stay';

    protected $stayService;
    protected $requestSettings;
    protected $mailService;
    protected $utilityService;
    protected $querySettingsServices;
    public  $utilsHosterServices;
    /**
     * Execute the console command.
     */

     public function __construct(StayService $_StayServices, UtilsHosterServices $_UtilsHosterServices,RequestSettingService $_RequestSettingService,MailService $_MailService,UtilityService $_UtilityService,QuerySettingsServices $_QuerySettingsServices)
     {
        parent::__construct();
        $this->stayService = $_StayServices;
        $this->requestSettings = $_RequestSettingService;
        $this->mailService = $_MailService;
        $this->utilityService = $_UtilityService;
        $this->querySettingsServices = $_QuerySettingsServices;
        $this->utilsHosterServices = $_UtilsHosterServices;

     }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->handleSendEmailPreCheckin();
    }

    public function handleSendEmailPreCheckin()
    {
        $currentTime = Carbon::now();
        $startOfHour = $currentTime->copy()->startOfHour(); // inicio hor actual
        $endOfHour = $currentTime->copy()->endOfHour();     // fin hora actuyal
        $hours = 48;

        // Obtener estancias cuyo checkin sera dentro de 48hrs
        $stays = Stay::select('id', 'hotel_id', 'check_in','check_out')
            ->whereDate('check_in', $currentTime->addHours($hours)->format('Y-m-d'))
            ->with([
                'queries' => function ($query) {
                    $query->select('id', 'stay_id', 'guest_id', 'answered', 'qualification','period')
                        ->where('period', 'pre-stay');
                },
                'queries.guest' => function ($query) {
                    $query->select('id', 'name', 'email');
                },
                'hotel' => function ($query) {
                    $query->select('id', 'name', 'checkout', 'checkin', 'subdomain', 'show_facilities', 'show_experiences', 'show_places', 'zone');
                }
            ])
            ->get();

            /* Log::info(json_encode([
                'message' => 'handleSendEmailPreCheckin estancias encontradas',
                'data' => [
                    'stays_count' => $stays->count(),
                    'stays' => $stays,
                    'current_time' => $currentTime->toDateTimeString(),
                    'start_of_hour' => $startOfHour->toDateTimeString(),
                    'end_of_hour' => $endOfHour->toDateTimeString()
                ]
            ], JSON_PRETTY_PRINT)); */

            Log::info('estancias encontradas en prechekin handleSendEmailPreCheckin: '.$stays->count());

        // Procesar cada estancia
        foreach ($stays as $stay) {
            // Manejar checkin nulo asignando la última hora del día
            $hotelCheckinTime = $stay->hotel->checkin
                ? Carbon::parse($stay->hotel->checkin)->addHours($hours)
                : Carbon::today()->endOfDay()->addHours($hours);


            // Verificar si la hora actual está dentro del rango de 48 horas antes del checkin
            if (!$currentTime->between($hotelCheckinTime->copy()->startOfHour(), $hotelCheckinTime->copy()->endOfHour())) {
                Log::info('Estancias fuera del rango de hora de 48 horas antes del checkin handleSendEmailPreCheckin', [
                    'stay_id' => $stay->id,
                    'stay_checkin' => $stay->check_in,
                ]);
                continue;
            }

            foreach ($stay->queries as $query) {
                if (!$query->guest || !$query->guest->email) {
                    Log::warning('Consulta sin huésped válido', ['query_id' => $query->id]);
                    continue;
                }
                $chainSubdomain = $stay->hotel->subdomain;

                if($stay->check_in && $stay->check_out){
                    $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
                    $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
                }
                $webappEditStay = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'editar-estancia/'.$stay->id);

                $checkData = [
                    'title' => "Datos de tu estancia en {$stay->hotel->name}",
                    'formatCheckin' => $formatCheckin ?? null,
                    'formatCheckout' => $formatCheckout,
                    'editStayUrl' => $webappEditStay,
                ];

                // Diferente lógica según el estado de `answered`
                $type = $query->answered ? 'post-checkout-answered' : 'post-checkout-unanswered';

                $chainSubdomain = $stay->hotel->subdomain;

                //urls
                $urlWebapp = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain);
                $webappLinkInbox = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox');
                $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox',"e={$stay->id}&g={$query->guest->id}&fill=VERYGOOD");
                $webappChatLink = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'chat');
                $urlCheckin = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,"mi-estancia/huespedes/completar-checkin/{$query->guest->id}");

                $queryData = [
                    'currentPeriod' => $query->period,
                    'webappLinkInbox' => $webappLinkInbox,
                    'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,
                    'answered' => $query->answered == 1 ? true : false
                ];

                //corosseling que trae instalaciones exp y destinos etc
                $crosselling = $this->utilityService->getCrossellingHotelForMail($stay->hotel, $chainSubdomain);

                $urlQr = generateQr($stay->hotel->subdomain, $urlWebapp);
                //$urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";


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

                Log::info('handleSendEmailPreCheckin email send', ['guest_email' => $query->guest->email, 'type' => $type]);
                //Log::info('handleSendEmailPreCheckin data email', ['dataEmail' => $dataEmail]);

                try {
                    $this->mailService->sendEmail(new prepareArrival($type, $stay->hotel, $query->guest, $dataEmail,true), $query->guest->email);
                    Log::info('Correo enviado correctamente handleSendEmailPreCheckin', ['guest_email' => $query->guest->email]);
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo handleSendEmailPreCheckin', [
                        'guest_email' => $query->guest->email,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
