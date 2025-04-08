<?php

namespace App\Console\Commands;

use App\Mail\Queries\InsistencePostStayResponse;
use App\Mail\Queries\RequestReviewGuest;
use App\Models\Stay;
use App\Services\RequestSettingService;
use Illuminate\Console\Command;
use App\Services\StayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\MailService;
use App\Mail\Guest\MsgStay;
use App\Mail\Guest\postCheckoutMail;
use App\Services\QuerySettingsServices;
use App\Services\UtilityService;
use stdClass;
use Illuminate\Support\Facades\DB;
class SendPostStayEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-post-stay-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'correos post stay';
    protected $stayService;
    protected $requestSettings;
    protected $mailService;
    protected $utilityService;
    protected $querySettingsServices;
    /**
     * Execute the console command.
     */

     public function __construct(StayService $_StayServices, RequestSettingService $_RequestSettingService,MailService $_MailService,UtilityService $_UtilityService,QuerySettingsServices $_QuerySettingsServices)
     {
         parent::__construct();
         $this->stayService = $_StayServices;
         $this->requestSettings = $_RequestSettingService;
         $this->mailService = $_MailService;
         $this->utilityService = $_UtilityService;
        $this->querySettingsServices = $_QuerySettingsServices;

     }

    public function handle()
    {
        $this->handleSendEmailPostChekin();
        $this->handleSendEmailPostCheckout();
        $this->handleSendEmailCheckout();
    }

    public function handleSendEmailCheckout()
    {
        Log::info('handleSendEmailCheckout init');
        // Rango de tiempo basado en la fecha actual
        $today = Carbon::today();

        // Obtener estancias con checkout en la fecha actual
        $stays = Stay::select('id', 'hotel_id', 'check_out')
            ->whereHas('hotel') // Validar que la estancia esté asociada a un hotel
            ->whereDate('check_out', $today) // Filtrar por la fecha de checkout
            ->with([
                'queries' => function ($query) {
                    $query->select('id', 'stay_id', 'guest_id', 'answered', 'qualification')
                        ->where('period', 'post-stay');
                },
                'queries.guest' => function ($query) {
                    $query->select('id', 'name', 'email');
                },
                'hotel' => function ($query) {
                    $query->select('id', 'name', 'checkout', 'subdomain','show_facilities','show_experiences','show_places','zone');
                }
            ])
            ->get();

        // Hora actual
        $currentTime = Carbon::now();

        Log::info('handleSendEmailCheckoutSend', ['stays_count' => $stays]);


        // Procesar cada estancia
        foreach ($stays as $stay) {
             // Manejar checkout nulo asignando la última hora del día
            $hotelCheckoutTime = $stay->hotel->checkout
                ? Carbon::parse($stay->hotel->checkout) // Si tienecheckout
                : Carbon::today()->endOfDay();          // Si es null, usar ultima hora del dia
            $type = 'checkout';

            // Verificar si la hora actual está dentro del rango de checkout del hotel
            if (!$currentTime->between($hotelCheckoutTime->copy()->startOfHour(), $hotelCheckoutTime->copy()->endOfHour())) {
                Log::info('Estancia fuera del rango de hora de checkout handleSendEmailCheckoutSend', ['stay_id' => $stay->id]);
                continue;
            }

            foreach ($stay->queries as $query) {
                if (!$query->guest || !$query->guest->email) {
                    Log::warning('Consulta sin huésped válido handleSendEmailCheckoutSend', ['query_id' => $query->id]);
                    continue;
                }

                $chainSubdomain = $stay->hotel->subdomain;
                $crosselling = $this->utilityService->getCrossellingHotelForMail($stay->hotel, $chainSubdomain);
                $urlWebapp = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain);
                $urlQr = generateQr($stay->hotel->subdomain, $urlWebapp);
                //$urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";

                    $currentPeriod = $this->stayService->getCurrentPeriod($stay->hotel, $stay);
                    $querySettings = $this->querySettingsServices->getAll($stay->hotel->id);
                    $hoursAfterCheckin = $this->stayService->calculateHoursAfterCheckin($stay->hotel, $stay);
                    $showQuerySection = true;

                    if(
                        $currentPeriod == 'pre-stay' && !$querySettings->pre_stay_activate ||
                        $currentPeriod == 'in-stay' && $hoursAfterCheckin < 24 ||
                        $currentPeriod == 'post-stay'
                    ){
                        $showQuerySection = false;
                    }
                    //
                    $webappLinkInbox = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox');
                    $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox',"e={$query->stay_id}&g={$query->guest_id}&fill=VERYGOOD");

                    $queryData = [
                        'showQuerySection' => $showQuerySection,
                        'currentPeriod' => $currentPeriod,
                        'webappLinkInbox' => $webappLinkInbox,
                        'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,

                    ];

                $dataEmail = [
                    'places' => $crosselling['places'],
                    'experiences' => $crosselling['experiences'],
                    'facilities' => $crosselling['facilities'],
                    'urlQr' => $urlQr,
                    'urlWebapp' => $urlWebapp,
                    'queryData' => $queryData
                ];

                try {
                    $queries_url = url('consultas?e=' . $stay->id . '&lang=' . $query->guest->lang_web . '&g=' . $query->guest->id);
                    $link = includeSubdomainInUrlHuesped($queries_url, $stay->hotel);


                    $this->mailService->sendEmail(new MsgStay($type, $stay->hotel, $query->guest, $dataEmail,true), $query->guest->email);
                    $this->mailService->sendEmail(new MsgStay($type, $stay->hotel, $query->guest, $dataEmail), 'francisco20990@gmail.com');
                    Log::info('Correo enviado correctamente handleSendEmailCheckout ', ['guest_email' => $query->guest->email]);
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo handleSendEmailCheckoutSend', [
                        'guest_email' => $query->guest->email,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }


    public function handleSendEmail()
    {
        Log::info('inicia send-post-stay-emails');
        $startTime = Carbon::now()->subHours(72)->startOfHour();
        $endTime = Carbon::now()->subHours(24)->startOfHour();
        Log::info('$startTime'.$startTime);
        Log::info('$endTime'.$endTime);
        // Filtra las estancias dentro de este rango de tiempo
        $stays = Stay::select('id','hotel_id','check_out')->whereHas('hotel')
            ->whereBetween('check_out', [$startTime->toDateString(), $endTime->toDateString()])
            ->with([
                'queries' => function($query) {
                    $query->select('id', 'stay_id','guest_id','answered','qualification')->where('period', 'post-stay');
                },
                'queries.guest' => function($query) {
                    $query->select('id', 'name','email');
                }
            ])
            ->get();
        Log::info('$stays'.$stays);
        foreach($stays as $stay){
            $checkoutDateTime = $this->stayService->getCheckoutDateTime($stay->id);
            $now = Carbon::now();
            $hoursDifference = $now->diffInHours($checkoutDateTime);
            Log::info('$stay'.$stay);
            Log::info('$hoursDifference'.$hoursDifference);
            foreach($stay->queries as $query){
                Log::info('$query '.json_encode($query));
                $queries_url = url('consultas?e='.$stay->id.'&lang='.$query->guest->lang_web.'&g='.$query->guest->id);
                $link = includeSubdomainInUrlHuesped($queries_url, $stay->hotel);
                Log::info('$link'.$link);
                if(intval($hoursDifference) == 48){
                    Log::info('answered '.boolval($query->answered));
                    if(!boolval($query->answered)){
                        Log::info('enviado a '.$query->guest->email);
                        Mail::to($query->guest->email)->send(new InsistencePostStayResponse($link, $stay->hotel));
                    }

                    $requestSettings = $this->requestSettings->getAll($stay->hotel->id);
                    $arr = json_decode($requestSettings->request_to);
                    $inArrayCondition = in_array('NORMAL',$arr);
                    Log::info('$requestSettings->request_to '.$requestSettings->request_to);
                    $goodArr = ['GOOD','VERYGOOD'];
                    $normalArr = ['GOOD','VERYGOOD','NORMAL'];

                    $condition1 = (!$inArrayCondition) && in_array($query->qualification,$goodArr) && boolval($query->answered);
                    $condition2 = ($inArrayCondition) && in_array($query->qualification,$normalArr) && boolval($query->answered);
                    $condition3 = ($inArrayCondition) && !boolval($query->answered);

                    Log::info('$condition1 '.json_encode($condition1));
                    Log::info('$condition2 '.json_encode($condition2));
                    Log::info('$condition3 '.json_encode($condition3));

                    if($condition1 || $condition2 || $condition3){
                        Mail::to($query->guest->email)->send(new RequestReviewGuest($link, $stay->hotel));
                    }
                }
            }
        }

    }

    public function handleSendEmailPostChekin()
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
            //relacion con hotel_communications
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
            ->where(function ($query) { //si no existe registro o existe pero no se ha enviado
                $query->whereNull('en.post_checkin') // No existe registro
                      ->orWhere('en.post_checkin', 0);
            })
            ->where(function ($query) {
                $query->whereNull('hc.post_checkin_email')
                      ->orWhere('hc.post_checkin_email', 1);
            })
            ->get();

        foreach($stays as $stay){
             // Marcar PRIMERO como enviado (evita condiciones de carrera)
            $inserted = DB::table('email_notifications')->updateOrInsert(
                ['stay_id' => $stay->id],
                [
                    'hotel_id' => $stay->hotelId,
                    'post_checkin' => 1,
                    'sent_at' => now(),
                ]
            );

            // Si ya existía un registro previo, no envíes el correo de nuevo
            if (!$inserted) {
                continue;
            }

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

                $this->stayService->guestWelcomeEmail(
                        'postCheckin',
                        $stay->chainSubdomain,
                        $hotel,
                        $guest,
                        $stay
                    );

            }

           /*  DB::table('email_notifications')->insert(
                [
                    'stay_id' => $stay->id,
                    'hotel_id' => $stay->hotelId,
                    'post_checkin' => 1,
                    'sent_at' => now()
                ]
            ); */

        }
    }


    public function handleSendEmailPostCheckout()
    {
        Log::info('handleSendEmailPostCheckout init');
        $currentTime = Carbon::now();
        $startOfHour = $currentTime->copy()->startOfHour(); // inicio hor actual
        $endOfHour = $currentTime->copy()->endOfHour();     // fin hora actuyal
        $hoursBack = 48;

        // Obtener estancias cuyo checkout fue exactamente hace 48 horas
        $stays = Stay::select('id', 'hotel_id', 'check_out')
            //->whereHas('hotel') // Validar que la estancia esté asociada a un hotel antes
            ->leftJoin('hotel_communications as hc', function ($join) { //con hotel communications
                $join->on('hc.hotel_id', '=', 'h.id')
                    ->where('hc.type', '=', 'email');
            })
            ->whereDate('check_out', $currentTime->subHours($hoursBack)->format('Y-m-d')) // Filtrar por la fecha de checkout
            ->with([
                'queries' => function ($query) {
                    $query->select('id', 'stay_id', 'guest_id', 'answered', 'qualification','period')
                        ->where('period', 'post-stay');
                },
                'queries.guest' => function ($query) {
                    $query->select('id', 'name', 'email');
                },
                'hotel' => function ($query) {
                    $query->select('id', 'name', 'checkout', 'subdomain', 'show_facilities', 'show_experiences', 'show_places', 'zone','city_id');
                }
            ])
            ->get();

        Log::info('handleSendEmailPostCheckout', [
            'stays_count' => $stays->count(),
            'current_time' => $currentTime,
            'start_of_hour' => $startOfHour,
            'end_of_hour' => $endOfHour,
        ]);

        // Procesar cada estancia
        foreach ($stays as $stay) {
            // Manejar checkout nulo asignando la última hora del día
            $hotelCheckoutTime = $stay->hotel->checkout
                ? Carbon::parse($stay->hotel->checkout)->subHours($hoursBack) // Sumar 48 horas al checkout del hotel
                : Carbon::today()->endOfDay()->subHours($hoursBack);          // Última hora del día + 48 horas


            // Verificar si la hora actual está dentro del rango de 48 horas después del checkout
            if (!$currentTime->between($hotelCheckoutTime->copy()->startOfHour(), $hotelCheckoutTime->copy()->endOfHour())) {
                Log::info('Estancia fuera del rango de hora de 48 horas después del checkout', [
                    'stay_id' => $stay->id,
                    'stay_checkout' => $stay->check_out,
                    'hotel_checkout_time' => $hotelCheckoutTime,
                    'starthotel' => $hotelCheckoutTime->copy()->startOfHour(),
                    'endhotel' => $hotelCheckoutTime->copy()->endOfHour(),
                ]);
                continue;
            }

            foreach ($stay->queries as $query) {
                if (!$query->guest || !$query->guest->email) {
                    Log::warning('Consulta sin huésped válido', ['query_id' => $query->id]);
                    continue;
                }

                // Diferente lógica según el estado de `answered`
                $type = $query->answered ? 'post-checkout-answered' : 'post-checkout-unanswered';

                $chainSubdomain = $stay->hotel->subdomain;
                $crosselling = $this->utilityService->getCrossellingHotelForMail($stay->hotel, $chainSubdomain);

                //urls
                $urlWebapp = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain);
                $reservationURl = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'reservar-estancia');
                $webappLinkInbox = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox');
                $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $stay->hotel->subdomain,'inbox',"e={$stay->id}&g={$query->guest->id}&fill=VERYGOOD");
                $urlQr = generateQr($stay->hotel->subdomain, $urlWebapp);
                //$urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";

                $queryData = [
                    'currentPeriod' => $query->period,
                    'webappLinkInbox' => $webappLinkInbox,
                    'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,
                    'answered' => $query->answered == 1 ? true : false
                ];

                $dataEmail = [
                    'places' => $crosselling['places'],
                    'experiences' => $crosselling['experiences'],
                    'facilities' => $crosselling['facilities'],
                    'urlQr' => $urlQr,
                    'urlWebapp' => $urlWebapp,
                    'queryData' => $queryData,
                    'reservationURl' => $reservationURl
                ];

                Log::info('handleSendEmailPostCheckout data email', ['dataEmail' => $dataEmail]);

                try {

                    $this->mailService->sendEmail(new postCheckoutMail($type, $stay->hotel, $query->guest, $dataEmail,true), $query->guest->email);
                    $this->mailService->sendEmail(new postCheckoutMail($type, $stay->hotel, $query->guest, $dataEmail,true), 'francisco20990@gmail.com');
                    Log::info('Correo enviado correctamente handleSendEmailPostCheckout', ['guest_email' => $query->guest->email]);

                } catch (\Exception $e) {
                    Log::error('Error al enviar correo handleSendEmailPostCheckout', [
                        'guest_email' => $query->guest->email,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }


}
