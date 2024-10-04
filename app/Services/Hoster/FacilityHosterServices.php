<?php

namespace App\Services\Hoster;

use App\Models\FacilityHoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FacilityHosterServices
{
    public function getAll ($request, $modelHotel) {
        try {
            
            $query = FacilityHoster::with(['images', 'translations'])
                ->where('hotel_id',$modelHotel->id)
                ->where(['status' => 1])->where('visible',1);
            
            if (isset($request->visible)) {
                $query = $query->where(['select' => $request->visible]);
            }
            
            $totalCount = (clone $query)->count();
            $totalVisibleCount = (clone $query)->where(['select' => 1])->count();
            $totalHiddenCount = (clone $query)->where(['select' => 0])->count();

            $offset = $request->offset ?? 0;
            $limit = $request->limit ?? $totalCount;//por default es el total ya que este metodo se usa en la webapp

            $facilities = $query->orderByRaw('
                CASE 
                    WHEN `select` = 1 THEN 0
                    ELSE 1
                END ASC,
                facility_hosters.order ASC
            ')->offset($offset)->limit($limit)->get();

            return [
                "facilities" => $facilities,
                "totalCount" => $totalCount,
                "totalVisibleCount" => $totalVisibleCount,
                "totalHiddenCount" => $totalHiddenCount,
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }


   
}
