<?php

namespace App\Console\Commands;

use App\Models\Stay;
use Carbon\Carbon;
use COM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use stdClass;
use Illuminate\Support\Facades\Log;
use Exception;


class SendQueriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendqueriescommand';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de consultas para stay y post-stay';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public $queryservice;
    public $settingsService;

    public function __construct(
        QueryServices $_queryservice,
        QuerySettingsServices $_settingsService,
    )
    {
        parent::__construct();
        $this->queryservice = $_queryservice;
        $this->settingsService = $_settingsService;
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $stays = Stay::select('stays.id','stays.hotel_id', 'hotels.checkin as hotel_checkin')
                    ->join('hotels', 'stays.hotel_id', '=', 'hotels.id')
                    ->with('guests:id')
                    ->whereDate('stays.check_out', '>', now()->subDays(11)->toDateString())
                    ->get();
            foreach($stays as $stay){
                $hotel = new stdClass();
                $hotel->checkin = $stay->hotel_checkin ?? null;    
                $period = $this->queryservice->getCurrentPeriod($hotel, $stay->id);    
                Log::info('stay para enviar las consultas: ' . $stay);
                if(!$period || $period == "invalid-stay") continue;
                Log::info('periodo de la estancia: ' . $period);
                if($period == 'pre-stay'){
                    $settings = $this->settingsService->getAll($stay->hotel_id);
                    if(!$settings->pre_stay_activate) continue;
                }

                if($period && $period !== 'pre-stay'){
                    foreach ($stay->guests as $g) {
                        Log::info('crear al huesped: ' . $g);
                        $this->queryservice->firstOrCreate($stay->id, $g->id, $period);    
                    }
                }
            }
            Log::info('sendqueriescommand ejecutado con exito');
        } catch (Exception $e) {
            Log::error('Ha ocurrido un error al enviar las consultas: ' . $e->getMessage());
        }
        
    }
}