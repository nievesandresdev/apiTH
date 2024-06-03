<?php

namespace App\Observers;

use App\Models\Stay;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;
use stdClass;

class StayObserver
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
     * Handle the Stay "created" event.
     */
    public function created(Stay $stay): void
    {
        //
    }

    /**
     * Handle the Stay "updated" event.
     */
    public function updated(Stay $stay)
    {
        if ($stay->isDirty('check_in') || $stay->isDirty('check_out')) {
            try{   
                $period = $this->queryservice->getCurrentPeriod($stay->hotel, $stay->id);    
                $disabled = false;
                Log::info('observer stay: $period ' . $period);
                $listGuests = $stay->guests()->get();
                if($period == 'pre-stay' || $period == 'in-stay'){
                    $settings = $this->settingsService->getAll($stay->hotel_id);
                    $periodKey = str_replace("-", "_", $period).'_activate';
                    Log::info('observer stay: $periodKey ' . $periodKey);
                    if(!$settings->$periodKey){
                        $disabled = true;
                    }  
                }
                
                if($period){
                    foreach ($listGuests as $guest) {
                        $query = $this->queryservice->firstOrCreate($stay->id, $guest->id, $period, $disabled);    
                        Log::info('observer stay: $query' . $query);
                    }   
                }   
                
            } catch (\Exception $e) {
                return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateStayObserver');
            }
        }
    }

    /**
     * Handle the Stay "deleted" event.
     */
    public function deleted(Stay $stay): void
    {
        //
    }

    /**
     * Handle the Stay "restored" event.
     */
    public function restored(Stay $stay): void
    {
        //
    }

    /**
     * Handle the Stay "force deleted" event.
     */
    public function forceDeleted(Stay $stay): void
    {
        //
    }
}
