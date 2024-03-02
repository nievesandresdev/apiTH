<?php

namespace App\Services;

use App\Models\Query;
use App\Models\QuerySetting;
use App\Models\Stay;
use App\Http\Resources\QueryResource;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueryServices {

    function __construct()
    {

    }

    public function findByParams ($request) {
        try {
            $stayId = $request->stayId ?? null;
            $guestId = $request->guestId ?? null;
            $period = $request->period ?? null;

            $query = Query::where(function($query) use($stayId, $guestId, $period){
                if ($stayId) {
                    $query->where('stay_id', $stayId);
                }
                if ($guestId) {
                    $query->where('guest_id', $guestId);
                }
                if ($period) {
                    $query->where('period', $period);
                }
            });
            $model = $query->first();

            $data = new QueryResource($model);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    //obtener periodo actual para la consulta
    public function getCurrentPeriod ($hotel, $stayId) {
        try {
            $stay =  Stay::find($stayId);
            $dayCheckin = $stay->check_in;
            $dayCheckout = $stay->check_out;
            $hourCheckin = $hotel->checkin;

            // Crear objeto Carbon para check-in
            $checkinDateTimeString = $dayCheckin . ' ' . $hourCheckin;
            $checkinDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkinDateTimeString);
            
            // período in-stay 
            $inStayStart = (clone $checkinDateTime)->addDay()->setTime(5, 0);
            $hideStart = Carbon::createFromFormat('Y-m-d', $dayCheckout);

             // período post-stay
            $postStayStart = Carbon::createFromFormat('Y-m-d H:i', $dayCheckout . ' 05:00');
            $postStayEnd = (clone $hideStart)->addDays(10);
            
            //fecha actual
            $now = Carbon::now();
            if ($now->lessThan($checkinDateTime)) {
                return 'pre-stay';
            }
            if ($now->greaterThanOrEqualTo($inStayStart) && $now->lessThan($hideStart)) {
                return 'in-stay';
            }
            if ($now->greaterThanOrEqualTo($postStayStart) && $now->lessThanOrEqualTo($postStayEnd)) {
                return 'post-stay';
            }
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getCurrentPeriod');
        }
    }

    public function firstOrCreate ($stayId, $guestId, $period) {
        try{
            return Query::firstOrCreate([
                'stay_id' => $stayId,
                'guest_id' => $guestId,
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.firstOrCreate');
        }
    }

    public function getResponses ($stayId, $guestId) {
        try{
            return Query::where('answered',1)
                        ->where('stay_id', $stayId)
                        ->where('guest_id', $guestId)
                        ->get();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.firstOrCreate');
        }
    }

    public function saveResponse ($id,$request) {
        try{
            $query = Query::find($id);
            $query->answered = true;
            $query->qualification = $request->qualification;
            $query->comment = $request->comment;
            $query->save();
            return $query; 
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveAnswer');
        }
    }

    
}