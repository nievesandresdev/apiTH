<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stay;
use App\Services\RequestSettingService;
use App\Services\StayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\MailService;
use App\Services\QuerySettingsServices;
use App\Mail\Guest\{prepareArrival};
use App\Services\UtilityService;
use App\Services\Hoster\UtilsHosterServices;
use Illuminate\Support\Facades\App;
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
        Log::info('handleSendEmailPreCheckin init', ['time' => Carbon::now()->format('Y-m-d H:i:s')]);
        $currentTime = Carbon::now();
        $targetDate = $currentTime->copy()->addHours(48)->format('Y-m-d');

        // Obtener estancias cuyo checkin sera dentro de 48hrs
        $stays = Stay::select('id', 'hotel_id', 'check_in','check_out')
            ->whereDate('check_in', $targetDate)
            ->with([
                'queries' => function ($query) {
                    $query->select('id', 'stay_id', 'guest_id', 'answered', 'qualification','period')
                        ->where('period', 'pre-stay')
                        ->with([
                            'guest' => function ($query) {
                                $query->select('id', 'name', 'email', 'off_email', 'lang_web');
                            }
                        ]);
                },
                'hotel' => function ($query) {
                    $query->select('id', 'name', 'checkout', 'checkin', 'subdomain', 'show_facilities', 'show_experiences', 'show_places', 'zone','city_id','sender_mail_mask')
                        ->with(['hotelCommunications' => function($query) {
                            $query->where('type', 'email');
                        }]);
                }
            ])
            ->get();

        Log::info('estancias encontradas en prechekin handleSendEmailPreCheckin: '.$stays->count());

        // Procesar cada estancia
        foreach ($stays as $stay) {
            // Manejar checkin nulo asignando la última hora del día
            $checkinDatetime = $stay->hotel->checkin
                ? Carbon::parse($stay->check_in . ' ' . $stay->hotel->checkin)
                : Carbon::parse($stay->check_in)->setHour(20)->setMinute(0)->setSecond(0);

            // Calcula la hora exacta 48 horas antes
            $sendWindowStart = $checkinDatetime->copy()->subHours(48)->startOfHour();
            $sendWindowEnd = $checkinDatetime->copy()->subHours(48)->endOfHour();

            // Evalúa si el cron está corriendo dentro de esa hora
            if (!$currentTime->between($sendWindowStart, $sendWindowEnd)) {
                Log::info('Estancias fuera del rango de hora de 48 horas antes del checkin handleSendEmailPreCheckin', [
                    'stay_id' => $stay->id,
                    'stay_checkin' => $stay->check_in,
                    'hotel_checkin_time' => $checkinDatetime->toDateTimeString(),
                    'hotel_checkin_time_start' => $checkinDatetime->copy()->startOfHour()->toDateTimeString(),
                    'hotel_checkin_time_end' => $checkinDatetime->copy()->endOfHour()->toDateTimeString(),
                    'current_time' => $currentTime->toDateTimeString(),
                    'hotelId' => $stay->hotel->id,
                ]);
                continue;
            }

            // Obtener la primera query pre-stay válida
            $query = $stay->queries->first();
            if (!$query || !$query->guest || !$query->guest->email) {
                Log::warning('Estancia sin query o huésped válido sendPreStayEmail', [
                    'stay_id' => $stay->id,
                    'queries_count' => $stay->queries->count(),
                    'queries' => $stay->queries->pluck('period')->toArray()
                ]);
                continue;
            }

            App::setLocale($query->guest->lang_web ?? 'es');

            $chainSubdomain = $stay->hotel->subdomain;

            if($stay->check_in && $stay->check_out){
                $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
                $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
            }
            $webappEditStay = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'editar-estancia/'.$stay->id);

            $checkData = [
                'title' => __('mail.stayCheckDate.title', ['hotel' => $stay->hotel->name]),
                'formatCheckin' => $formatCheckin ?? null,
                'formatCheckout' => $formatCheckout,
                'editStayUrl' => $webappEditStay,
            ];

            $chainSubdomain = $stay->hotel->subdomain;

            //urls
            $urlWebapp = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain);
            $webappLinkInbox = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox',"e={$stay->id}&g={$query->guest->id}");
            $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox',"e={$stay->id}&g={$query->guest->id}&fill=VERYGOOD");
            $webappChatLink = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'chat',"e={$stay->id}&g={$query->guest->id}");
            $urlCheckin = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,"mi-estancia/huespedes/completar-checkin/{$query->guest->id}");
            $urlPrivacy = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'privacidad',"e={$stay->id}&g={$query->guest->id}&email=true&lang={$query->guest->lang_web}");
            $urlFooterEmail = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'no-notificacion',"e={$stay->id}&g={$query->guest->id}");

            $queryData = [
                'currentPeriod' => $query->period,
                'webappLinkInbox' => $webappLinkInbox,
                'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,
                'answered' =>  $query->disabled && $query->period == 'pre-stay'
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
                'urlPrivacy' => $urlPrivacy,
                'urlFooterEmail' => $urlFooterEmail
            ];

            Log::info('handleSendEmailPreCheckin email send', ['guest_email' => $query->guest->email]);

            $communication = $stay->hotel->hotelCommunications->firstWhere('type', 'email');
            $shouldSend = !$communication || $communication->pre_checkin_email;

            try {
                if(!$query->guest->off_email){
                    if($shouldSend){
                        $this->mailService->sendEmail(new prepareArrival('prepare-arrival', $stay->hotel, $query->guest, $dataEmail,true), $query->guest->email);
                        Log::info('Correo enviado correctamente handleSendEmailPreCheckin', ['guest_email' => $query->guest->email]);
                    }else{
                        Log::info('Correo no enviado handleSendEmailPreCheckin', ['guest_email' => $query->guest->email]);
                    }
                }else{
                    Log::info("No se envía correo preCheckin email_off a {$query->guest->email} (Estancia ID: {$stay->id}, Hotel: {$stay->hotel->name})");
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar correo handleSendEmailPreCheckin', [
                    'guest_email' => $query->guest->email,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }
}
