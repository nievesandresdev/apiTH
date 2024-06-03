<?php

namespace App\Observers;

use App\Models\Stay;
use App\Models\StayAccess;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use stdClass;

class StayAccessObserver
{
    public $queryservice;
    public $settingsService;

    public function __construct(
        QueryServices $_queryservice,
        QuerySettingsServices $_settingsService,
        
    ) {
        $this->queryservice = $_queryservice;
        $this->settingsService = $_settingsService;
    }
    /**
     * Handle the StayAccess "created" event.
     */
    public function created(StayAccess $stayAccess)
    {
        try{   
            $disabled = false;
            $stay = Stay::select('stays.id','stays.hotel_id', 'hotels.checkin as hotel_checkin')
                    ->where('stays.id',$stayAccess->stay_id)
                    ->join('hotels', 'stays.hotel_id', '=', 'hotels.id')
                    ->first();
            Log::info('observer access: $stay' . $stay);
            
            $period = $this->queryservice->getCurrentPeriod($stay->hotel, $stay->id);    
            Log::info('observer access: $period' . $period);
            if($period == 'pre-stay' || $period == 'in-stay'){
                $settings = $this->settingsService->getAll($stay->hotel_id);
                $periodKey = str_replace("-", "_", $period).'_activate';
                if(!$settings->$periodKey){
                    $disabled = true;
                }  
            }
            
            if($period){
                $query = $this->queryservice->firstOrCreate($stay->id, $stayAccess->guest_id, $period, $disabled);    
                Log::info('observer access: $query' . $query);
            }   
            
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.StayAccessObserver');
        }
    }
}
