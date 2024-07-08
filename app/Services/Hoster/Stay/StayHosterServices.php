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

  

    public function getAllByHotel ($hotel) {
        try {
            // return Stay::where('hotel_id',$hotel->id)->get();
            $stays = Stay::
            with([
                'chats:id,stay_id,pending',
                'chats.messages:by,chat_id,status',
                'guests:acronym,color',
            ])
            ->select([
                'stays.id',
                'stays.updated_at',
                'stays.room',
                'stays.number_guests',
                'stays.check_out',
                'stays.check_in',
                'stays.pending_queries_seen',
                DB::raw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.attended = 0 AND queries.answered = 1) as pending_queries_count'),
                DB::raw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.answered = 1) as answered_queries_count'),
                DB::raw("(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.answered = 1 AND queries.qualification = 'GOOD') as good_queries_count"),
                DB::raw('(SELECT COUNT(*) FROM stay_accesses as sacc WHERE sacc.stay_id = stays.id) as accesses_count'),
                DB::raw('(SELECT COUNT(*) FROM note_guests as ng WHERE ng.stay_id = stays.id) as guests_notes_count'),
                DB::raw('(SELECT MAX(pending) FROM chats WHERE chats.stay_id = stays.id) as has_pending_chats')
            ])
            ->groupBy('stays.id', 'stays.updated_at', 'stays.room', 'stays.number_guests', 'stays.check_out', 'stays.check_in', 'stays.pending_queries_seen')
            ->orderByRaw('
                CASE 
                    WHEN has_pending_chats = 1 THEN 0
                    WHEN pending_queries_count > 0 THEN 1
                    WHEN stays.room IS NULL OR stays.room = "" AND CURDATE() BETWEEN stays.check_in AND stays.check_out THEN 2
                    ELSE 3
                END
            ')
            ->where('hotel_id',$hotel->id)
            ->orderBy('stays.updated_at', 'DESC')
            ->get();


            foreach ($stays as $stay) {
                $stay['period']  = $this->queryService->getCurrentPeriod($hotel, $stay->id);
            }
            return $stays;
        } catch (\Exception $e) {
            return $e;
        }
    }

}
