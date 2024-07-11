<?php

namespace App\Services\Hoster\Stay;

use App\Models\hotel;
use App\Models\Stay;
use App\Services\QueryServices;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class StayHosterServices {
    
    public $queryService;

    function __construct(
        QueryServices $_QueryServices
    )
    {
        $this->queryService = $_QueryServices;
    }

    // 'stays.pending_queries_seen',
    // DB::raw("(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.answered = 1 AND queries.qualification = 'GOOD') as good_queries_count"),
    // DB::raw('(SELECT COUNT(*) FROM stay_accesses as sacc WHERE sacc.stay_id = stays.id) as accesses_count'),
    // DB::raw('(SELECT COUNT(*) FROM note_guests as ng WHERE ng.stay_id = stays.id) as guests_notes_count'),
    public function getAllByHotel($hotel, $filters) {
        try {
            $now = Carbon::now()->format('Y-m-d H:i');
            $query = Stay::with([
                    'chats:id,stay_id,pending',
                    'chats.messages:by,chat_id,status',
                    'guests:acronym,color,lang_web',
                ])
                ->select([
                    'stays.id',
                    'stays.updated_at',
                    'stays.room',
                    'stays.number_guests',
                    'stays.check_out',
                    'stays.check_in',
                    DB::raw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.attended = 0 AND queries.answered = 1) as pending_queries_count'),
                    DB::raw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.answered = 1) as answered_queries_count'),
                    DB::raw('(SELECT MAX(pending) FROM chats WHERE chats.stay_id = stays.id) as has_pending_chats'),
                    DB::raw("CASE 
                                WHEN '$now' < DATE_FORMAT(stays.check_in, CONCAT('%Y-%m-%d ', COALESCE((SELECT checkin FROM hotels WHERE hotels.id = stays.hotel_id), '16:00'))) THEN 'pre-stay'
                                WHEN '$now' >= DATE_FORMAT(stays.check_in, CONCAT('%Y-%m-%d ', COALESCE((SELECT checkin FROM hotels WHERE hotels.id = stays.hotel_id), '16:00'))) AND '$now' < stays.check_out THEN 'in-stay'
                                WHEN '$now' >= stays.check_out AND '$now' THEN 'post-stay'
                             END as period")
                ])
                ->where('hotel_id', $hotel->id);
    
            if (!empty($filters['periods'])) {
                $query->havingRaw("period IN ('" . implode("','", $filters['periods']) . "')");
            }
    
            if (!empty($filters['search'])) {
                $query->whereHas('guests', function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%');
                });
            }

            if (isset($filters['pendings']) && $filters['pendings'] == 'pending') {
                $query->where(function($q) {
                    $q->whereRaw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.attended = 0 AND queries.answered = 1) > 0')
                      ->orWhereRaw('(SELECT MAX(pending) FROM chats WHERE chats.stay_id = stays.id) > 0');
                });
            }
    
            $stays = $query->orderBy('stays.updated_at', 'DESC')->get();
    
            // Counts for all, each period, and pending
            $totalCounts = $stays->count();
            $countsByPeriod = $stays->groupBy('period')->mapWithKeys(function ($items, $period) {
                return [$period => $items->count()];
            });
            $pendingCounts = $stays->filter(function ($stay) {
                return $stay->pending_queries_count > 0 || $stay->has_pending_chats > 0;
            })->count();
    
            return [
                'stays' => $stays,
                'total_counts' => $totalCounts,
                'counts_by_period' => $countsByPeriod,
                'pending_counts' => $pendingCounts
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function statisticsByHotel($hotel) {
        try {
            $todayDay = Carbon::now()->format('d');
            $month = ucfirst(Carbon::now()->locale('es')->isoFormat('MMMM'));
            $today = Carbon::now()->format('Y-m-d');
            $dataStays = $this->getAllByHotel($hotel, []);

            $checkinToday = 0;
            $checkoutToday = 0;
            $totalGuests = 0;
            $countsByPeriod = ['pre-stay' => 0,'in-stay' => 0,'post-stay' => 0];
            $guestsByPeriod = ['pre-stay' => 0,'in-stay' => 0,'post-stay' => 0];
            $langsTotal = ['es' => 0,'fr' => 0,'en' => 0];
            $percentageLangs = ['es' => 0,'fr' => 0,'en' => 0];
            
            foreach($dataStays['stays'] as $stay){
                //today data
                $today == $stay->check_in ? $checkinToday++:'';
                $today == $stay->check_out ? $checkoutToday++:'';
                //guests counter
                $guestsByPeriod[$stay->period] += count($stay->guests);
                $totalGuests += count($stay->guests);
                foreach($stay->guests as $guest){
                    $langsTotal[strtolower($guest->lang_web)] += 1;
                }
                //stays
                $countsByPeriod[$stay->period] += 1;
            }

            
            foreach(['es','en','fr'] as $lang){
                $percentageLangs[$lang] = round(($langsTotal[$lang]/$totalGuests)*100);
            }

            return [
                'today' => $todayDay,
                'month' => $month,
                'checkinToday' => $checkinToday,
                'checkoutToday' => $checkoutToday,
                'countsByPeriod' => $countsByPeriod,
                'guestsByPeriod' => $guestsByPeriod,
                'totalGuests' => $totalGuests,
                'langsTotal' => $langsTotal,
                'percentageLangs' => $percentageLangs
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }
    

}
