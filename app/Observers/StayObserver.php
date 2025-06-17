<?php

namespace App\Observers;

use App\Models\Query;
use App\Models\Stay;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use App\Utils\Enums\EnumResponse;
use Carbon\Carbon;
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
                $originalStay = $stay->getOriginal();
                $originalPeriod = $this->getOriginalPeriod($stay->hotel, $originalStay);
                // Log::info('originalPeriod '.$originalPeriod);
                $currentPeriod = $this->queryservice->getCurrentPeriod($stay->hotel, $stay->id);    
                // Log::info('currentPeriod '.$currentPeriod);
                $resetQueryPost = $originalPeriod == 'post-stay' && ($currentPeriod == 'in-stay' || $currentPeriod == 'pre-stay');
                $resetQueryIn = ($originalPeriod == 'post-stay' || $originalPeriod == 'in-stay') && $currentPeriod == 'pre-stay';

                $disabled = false;
                // Log::info('observer stay: $currentPeriod ' . $currentPeriod);
                $listGuests = $stay->guests()->get();
                if($currentPeriod == 'pre-stay' || $currentPeriod == 'in-stay'){
                    $settings = $this->settingsService->getAll($stay->hotel_id);
                    $periodKey = str_replace("-", "_", $currentPeriod).'_activate';
                    // Log::info('observer stay: $periodKey ' . $periodKey);
                    if(!$settings->$periodKey){
                        $disabled = true;
                    }  
                }
                
                $arrUpdate = [ 
                    'answered' => false,
                    'qualification' => null,
                    'comment' => null,
                    'attended' => false,
                    'visited' => false
                ];
                foreach ($listGuests as $guest) {
                    //actualizar queries
                    if($currentPeriod){
                        $query = $this->queryservice->firstOrCreate($stay->id, $guest->id, $currentPeriod, $disabled);    
                        // Log::info('observer stay: $query' . $query);
                    }   
                    if($resetQueryPost){
                        $postQuery = Query::where('guest_id',$guest->id)
                            ->where('stay_id',$stay->id)
                            ->where('period','post-stay')
                            ->first();
                        if($postQuery && $postQuery->histories()->count() > 0){
                            $postQuery->histories()->delete();
                        }
                        if($postQuery){
                            $postQuery->delete();
                        }
                        
                        
                        // $this->queryservice->updateParams( $postQuery->id, $arrUpdate);
                        // Log::info('reset postQuery');
                    }
                    if($resetQueryIn ){
                        // Log::info('rest InQuery');
                        // $InQuery = $guest->queries()->where('period','in-stay')->delete();
                        $InQuery = Query::where('guest_id',$guest->id)
                            ->where('stay_id',$stay->id)
                            ->where('period','in-stay')
                            ->first();
                        if($InQuery && $InQuery->histories()->count() > 0){
                            $InQuery->histories()->delete();
                        }
                        if($InQuery){
                            $InQuery->delete();
                        }
                        // $this->queryservice->updateParams( $InQuery->id, $arrUpdate);
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

    public function getOriginalPeriod($hotel, $stay) {
        try {
            $dayCheckin = $stay['check_in'];
            $dayCheckout = $stay['check_out'];
            $hourCheckin = $hotel->checkin ?? '16:00';

            // Crear objeto Carbon para check-in
            $checkinDateTimeString = $dayCheckin . ' ' . $hourCheckin;
            $checkinDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkinDateTimeString);

            // período in-stay
            // $inStayStart = (clone $checkinDateTime)->addDay()->setTime(5, 0);
            $hideStart = Carbon::createFromFormat('Y-m-d', $dayCheckout);

             // período post-stay
            $postStayStart = Carbon::createFromFormat('Y-m-d H:i', $dayCheckout . ' 05:00');
            $postStayEnd = (clone $hideStart)->addDays(10);

            //fecha actual
            $now = Carbon::now();
            if ($now->lessThan($checkinDateTime)) {
                return 'pre-stay';
            }
            // if ($now->greaterThanOrEqualTo($inStayStart) && $now->lessThan($hideStart)) {
            if ($now->greaterThan($checkinDateTime) && $now->lessThan($hideStart)) {
                return 'in-stay';
            }
            if ($now->greaterThanOrEqualTo($postStayStart) && $now->lessThanOrEqualTo($postStayEnd)) {
                return 'post-stay';
            }
             // Nueva condición para verificar si han pasado más de 10 días después del checkout
            if ($now->greaterThan($postStayEnd)) {
                //return 'invalid-stay';
                return 'post-stay';
            }
            return null;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
