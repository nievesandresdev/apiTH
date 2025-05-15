<?php

namespace App\Console\Commands;

use App\Mail\Queries\ReportHoster;
use App\Models\Query;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\Hoster\Users\UserServices;
use App\Services\MailService;
use Carbon\Carbon;

class TrackingNotification extends Command
{
    protected $userServices;
    protected $notificationFiltersInformGeneral;
    protected $specificChannels;
    protected $mailService;
    public function __construct(UserServices $userServices, MailService $mailService)
    {
        parent::__construct();
        $this->userServices = $userServices;
        $this->mailService = $mailService;
        $this->notificationFiltersInformGeneral = [
            'informGeneral' => true,
        ];

        $this->specificChannels = ['email'];
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:notification {--month : Run monthly tracking} {--week : Run weekly tracking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'informe de seguimiento semanal y mensual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('month')) {
            $this->monthlyTracking();
        } elseif ($this->option('week')) {
            $this->weeklyTracking();
        } else {
            $this->error('no selecciono ninguna opcion');
            return 1;
        }

        return 0;
    }

    /**
     * mes
     */
    protected function monthlyTracking()
    {
        Log::info('inicia cron mes');
        //filtros de notificaciones
        $from = now()->startOfMonth()->format('Y-m-d'); // Primer día del mes actual
        $to = now()->subDay()->format('Y-m-d');         // Ayer (último día del mes pasad
         $this->getUsersInformGeneral($this->notificationFiltersInformGeneral, $this->specificChannels, 1, $from, $to);
        
        
    }

    /**
     * semana
     */
    protected function weeklyTracking()
    {
        Log::info('inicia cron semana');
        $from = now()->subWeek()->startOfWeek()->format('Y-m-d'); // Inicio de la semana pasada
        $to = now()->subWeek()->endOfWeek()->format('Y-m-d');     // Fin de la semana pasada    
        $this->getUsersInformGeneral($this->notificationFiltersInformGeneral, $this->specificChannels, 2, $from, $to);
    }



    protected function getUsersInformGeneral($notificationFilters, $specificChannels, $periodicity, $from, $to)
    {
        try {
            Log::info('Iniciando getUsersInformGeneral con periodicity: ' . $periodicity);
            Log::info('from: ' . $from);
            Log::info('to: ' . $to);
            $usersByChannel = $this->userServices->getUsersWithNotifications($notificationFilters, $specificChannels, $periodicity);
            
            // Primero creamos un mapa de hoteles a usuarios
            $hotelsToUsers = [];
            
            foreach ($usersByChannel as $channel => $users) {
                foreach ($users as $user) {
                    foreach ($user->hotel as $hotel) {
                        $hotelId = $hotel->id;
                        if (!isset($hotelsToUsers[$hotelId])) {
                            $hotelsToUsers[$hotelId] = [];
                        }
                        $hotelsToUsers[$hotelId][] = $user;
                    }
                }
            }
            foreach ($hotelsToUsers as $hotelId => $users) {
                Log::info("Procesando hotel ID: $hotelId");
                
                // Obtenemos las estadísticas para este hotel (solo una consulta por hotel)
                $hotelStats = $this->getStats($hotelId, $from, $to);
                if ($hotelStats) {
                    foreach ($users as $user) {
                        $saasUrl = config('app.hoster_url');
                        $periodReport = $periodicity === 1 ? 'monthly' : 'weekly';
                        $links = [
                            'urlToReport' => "{$saasUrl}/seguimiento/general-report?periodType={$periodReport}&from={$from}&to={$to}&redirect=view&code={$user->login_code}",
                            'urlComunications' => "{$saasUrl}/comunicaciones?redirect=view&code={$user->login_code}",
                            'urlPromotions' => "{$saasUrl}/promociona-webapp?redirect=view&code={$user->login_code}",
                        ];
                        Log::info("Enviando reporte del hotel $hotelId al usuario: " . $user->email);
                        if ($this->hasAccents($user->email)) {
                            Log::info("El email $user->email tiene caracteres no ASCII, se omite");
                            continue;
                        }

                        $this->mailService->sendEmail(new ReportHoster($hotel, true, $hotelStats, $links), $user->email);
                    }
                } else {
                    Log::info("No hay estadísticas disponibles para el hotel $hotelId");
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getUsersInformGeneral: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    protected function getStats($hotelId, $from, $to)
    {
        $qs = Query::join('stays','queries.stay_id','stays.id')
        ->where('stays.hotel_id', $hotelId)
        ->where('queries.answered', 1)
        ->whereIn('queries.period',['in-stay','post-stay'])
        // filtro por intervalo de fechas (solo fecha)
        ->whereDate('queries.responded_at','>=',$from)
        ->whereDate('queries.responded_at','<=',$to)
        ->select(
                'queries.period','queries.guest_id','queries.answered','queries.qualification','queries.comment','queries.responded_at','queries.response_lang',
                'stays.hotel_id','stays.check_in','stays.check_out','stays.id as stayId'
            )
        ->get();
        // return $qs;
        $quals = ['VERYGOOD','GOOD','NORMAL','WRONG','VERYWRONG'];
        // Función auxiliar para procesar un sub-conjunto
        $makeStats = function($collection) use($quals){
            $total = $collection->count() ?: 1; // evitar división por cero
            return collect($quals)->map(function($q) use($collection,$total){
                $cnt = $collection->where('qualification',$q)->count();
                return [
                    'qualification' => $q,
                    'count'         => $cnt,
                    'percent'       => round($cnt / $total * 100, 1),
                ];
            });
        };

        // 4) Construyes los tres módulos
        $stats = [
            'from' => Carbon::parse($from)->format('d/m/Y'),
            'to' => Carbon::parse($to)->format('d/m/Y'),
            'all' => [
                'total'   => $qs->count(),
                'comments_count'=> $qs->whereNotNull('comment')->count(),
                'breakdown'=> $makeStats($qs),
            ],
            'in_stay' => tap([
                'total'   => $qs->where('period','in-stay')->count(),
                'comments_count'=> $qs->where('period','in-stay')->whereNotNull('comment')->count(),
            ], function(&$m) use($qs,$makeStats){
                $m['breakdown'] = $makeStats($qs->where('period','in-stay'));
            }),
            'post_stay' => tap([
                'total'   => $qs->where('period','post-stay')->count(),
                'comments_count'=> $qs->where('period','post-stay')->whereNotNull('comment')->count(),
            ], function(&$m) use($qs,$makeStats){
                $m['breakdown'] = $makeStats($qs->where('period','post-stay'));
            }),
        ];
        return $stats;
    }

    protected function hasAccents($email) {
        // Detecta si hay caracteres fuera del rango ASCII básico
        return preg_match('/[^\x00-\x7F]/', $email);
    }
}
