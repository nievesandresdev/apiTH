<?php

namespace App\Services\Hoster\Stay;

use App\Models\hotel;
use App\Models\Stay;
use App\Services\Hoster\UtilsHosterServices;
use App\Services\QueryServices;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StayHosterServices {
    
    public $queryService;
    public $utilsServices;

    function __construct(
        QueryServices $_QueryServices,
        UtilsHosterServices $_UtilsHosterServices
    )
    {
        $this->queryService = $_QueryServices;
        $this->utilsServices = $_UtilsHosterServices;
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
    
            if (!empty($filters['search'])) {
                $query->whereHas('guests', function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%');
                });
            }
            
            $allStays = (clone $query)->get();

            if (!empty($filters['periods'])) {
                $query->havingRaw("period IN ('" . implode("','", $filters['periods']) . "')");
            }
            

            if (isset($filters['pendings']) && $filters['pendings'] == 'pending') {
                $query->where(function($q) {
                    $q->whereRaw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.attended = 0 AND queries.answered = 1) > 0')
                      ->orWhereRaw('(SELECT MAX(pending) FROM chats WHERE chats.stay_id = stays.id) > 0');
                });
            }
    
            $stays = $query->orderByRaw('
                CASE 
                    WHEN has_pending_chats = 1 OR pending_queries_count > 0 THEN 0
                    ELSE 1
                END
            ')->orderBy('stays.updated_at', 'DESC')->get();
            // WHEN stays.room IS NULL OR stays.room = "" AND CURDATE() BETWEEN stays.check_in AND stays.check_out THEN 2
    
            // Counts for all, each period, and pending
            $totalValidCount = $stays->where('period','!=','post-stay')->count();

            $totalCount = $stays->count();
            $countsByPeriod = $stays->groupBy('period')->mapWithKeys(function ($items, $period) {
                return [$period => $items->count()];
            });
            
            //conteos general
            $countsGeneralByPeriod = $allStays->groupBy('period')->mapWithKeys(function ($items, $period) {
                return [$period => $items->count()];
            });

            $pendingCountsByPeriod = $allStays->reduce(function ($carry, $stay) {
                if ($stay->pending_queries_count > 0 || $stay->has_pending_chats > 0) {
                    if (!isset($carry[$stay->period])) {
                        $carry[$stay->period] = 0;
                    }
                    $carry[$stay->period]++;
                }
                return $carry;
            }, []);

    
            
            return [
                'stays' => $stays,
                'total_count' => $totalCount,
                'total_valid_count' => $totalValidCount,
                'counts_by_period' => $countsByPeriod,
                'counts_general_by_period' => $countsGeneralByPeriod,
                'pending_counts_by_period' => $pendingCountsByPeriod
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
                if($langsTotal[$lang] > 0){
                    $percentageLangs[$lang] = round(($langsTotal[$lang]/$totalGuests)*100);
                }
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

    public function getdetailData($stayId, $hotel) {
        try {
            $stay = Stay::find($stayId);

            $periodStay = $this->queryService->getCurrentPeriod($hotel, $stay->id);
            
            //detalle de estancia
            if($periodStay == 'pre-stay'){
                $untilCheckin = $this->utilsServices->calculateDaysUntilTo($stay->check_in);
                $textDay = $untilCheckin == 1 ? ' dia' : ' dias';
                $detailPeriod =  "Llega en <b>$untilCheckin</b> $textDay";
            }else if($periodStay == 'in-stay'){
                $totalDays = $this->utilsServices->calculateDaysBetween($stay->check_in, $stay->check_out);
                $currentNight = $this->calculateCurrentNight($stay->check_in, $stay->check_out);
                $detailPeriod = "noche <b>$currentNight</b> de <b>$totalDays</b>";
            }else{
                $detailPeriod = $this->utilsServices->calculateDaysOrWeeksFromDate($stay->check_out);
            }
            $formatCheckin = $this->utilsServices->formatDateToDayMonthAndYear($stay->check_in);
            $formatCheckout = $this->utilsServices->formatDateToDayMonthAndYear($stay->check_out);

            $guests = $stay->guests()->select('guests.id','guests.name','guests.lang_web','guests.acronym','guests.color')->get();

            $url_stay_icon = 'https://ui-avatars.com/api/?name=ES&color=fff&background=34A98F';
            $optionsListNote = [
                ['img' => $url_stay_icon, 'label' => 'Estancia', 'value' => 'STAY']
            ];
            foreach ($guests as $key => $g) {
                $url_g_icon = "https://ui-avatars.com/api/?name=$g->acronym&color=fff&background=$g->color";
                array_push($optionsListNote, ['img' => $url_g_icon, 'label' => $g->name, 'value' => $g->id]);
            }

            $notes = $this->getAllNotesByStay($stay->id);
           return [
                "detailPeriod" => $detailPeriod,
                "formatCheckin" => $formatCheckin,
                "formatCheckout" => $formatCheckout,
                "period" => $periodStay,
                "room" => $stay->room,
                "id" => $stay->id,
                "middle_reservation" => $stay->middle_reservation,
                "sessions" => $stay->sessions,
                "guests" => $guests,
                'optionsListNote' => $optionsListNote
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getdetailDateData($stayId, $hotel) {
        try {
            $periodStay = $this->queryService->getCurrentPeriod($hotel, $stayId);

            $stay = Stay::find($stayId);
            $stayCheckin = $stay->check_in;
            $stayCheckout = $stay->check_out;
            
            //detalle de estancia
            if($periodStay == 'pre-stay'){
                $untilCheckin = $this->utilsServices->calculateDaysUntilTo($stayCheckin);
                $textDay = $untilCheckin == 1 ? ' dia' : ' dias';
                $detailPeriod =  "Llega en <b>$untilCheckin</b> $textDay";
            }else if($periodStay == 'in-stay'){
                $totalDays = $this->utilsServices->calculateDaysBetween($stayCheckin, $stayCheckout);
                $currentNight = $this->calculateCurrentNight($stayCheckin, $stayCheckout);
                $detailPeriod = "noche <b>$currentNight</b> de <b>$totalDays</b>";
            }else{
                $detailPeriod = $this->utilsServices->calculateDaysOrWeeksFromDate($stayCheckout);
            }
            $formatCheckin = $this->utilsServices->formatDateToDayMonthAndYear($stayCheckin);
            $formatCheckout = $this->utilsServices->formatDateToDayMonthAndYear($stayCheckout);

           return [
                "detailPeriod" => $detailPeriod,
                "formatCheckin" => $formatCheckin,
                "formatCheckout" => $formatCheckout,
                "period" => $periodStay,
                "stayCheckin" => $stay->check_in,
                "stayCheckout" => $stay->check_out,
                "room" => $stay->room,
                "middle_reservation" => $stay->middle_reservation,
                "period" => $periodStay,
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateData($stayId, $data) {
        try {
            
            $stay = Stay::find($stayId);
            $stay->room = $data->room ?? $stay->room;
            $stay->middle_reservation = $data->middle_reservation ?? $stay->middle_reservation;
            $stay->sessions = $data->sessions ?? $stay->sessions;

            $save = $stay->save();
            if($save){
                sendEventPusher('private-update-stay-list-hotel.' . $stay->hotel_id, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
            }
            return $save;
            // Cambios guardados con éxito
        } catch (\Exception $e) {
            return $e;
        }

    }

    public function calculateCurrentNight($checkinDate, $checkoutDate) {
        
        $checkin = Carbon::parse($checkinDate);
        $checkout = Carbon::parse($checkoutDate);
        $now = Carbon::now();

        // Verify if the current date is within the stay period
        if ($now->between($checkin, $checkout, true)) {
            // Calculate the difference in days from the check-in date
            $currentNight = $checkin->diffInDays($now) + 1; // Adding 1 because nights are 1-indexed
            return $currentNight;
        } else {
            return null; // Return null if the current date is not within the stay period
        }
    }

    public function getSessions($stayId) {
        try {
            $stay = Stay::select('sessions')
                    ->where('id',$stayId)
                    ->first();
            return $stay->sessions;
        } catch (\Exception $e) {
            return $e;
        }
    }
    //notes 

    public function getAllNotesByStay($stayId){
        return DB::select("
            (SELECT ns.id, ns.content, ns.created_at, ns.updated_at, ns.edited, NULL as guest_id, NULL as guest_name, NULL as guest_acronym, NULL as guest_color, 'ES' as type
            FROM note_stays ns
            WHERE ns.stay_id = :stayId1)

            UNION ALL

            (SELECT ng.id, ng.content, ng.created_at, ng.updated_at, ng.edited, ng.guest_id, g.name as guest_name, g.acronym as guest_acronym, g.color as guest_color, 'HU' as type
            FROM note_guests ng
            LEFT JOIN guests g ON ng.guest_id = g.id
            WHERE ng.stay_id = :stayId2)

            ORDER BY created_at DESC
        ", ['stayId1' => $stayId, 'stayId2' => $stayId]);
    }

    //sessions
    public function createSession($data) {
        try {
            $stayId = $data->stayId;
            $field = $data->field;
            $userColor = $data->userColor;
            $userEmail = $data->userEmail;
            $userName = $data->userName;
            
            $stay = Stay::find($stayId);
            if($stay->sessions){
                $sessions = $stay->sessions ?? []; 
                // Verifica si el email ya existe en los arrays guardados
                foreach ($sessions as $session) {
                    if ($session['userEmail'] === $userEmail) {
                        return $stay->sessions;
                    }
                }
                // Si el email no existe, agrega el nuevo usuario a la lista
                $sessions[] = ['userColor' => $userColor, 'userEmail' => $userEmail, 'userName' => $userName];
        
                $stay->sessions = $sessions;
                $stay->save();
            }else{
                $stay->sessions = [['userColor'=>$userColor,'userEmail'=>$userEmail,'userName'=>$userName]];
            }
            $stay->save();
            return $stay->sessions;
        } catch (\Exception $e) {
            return $e;
        }
            
    }

    public function deleteSession($stayId, $userEmail) {
        try {
            $stay = Stay::find($stayId);
            Log::info('deleteSession hotel_id:'. $stay->hotel_id);
            $sessions = $stay->sessions ?? [];
    
            // Filtra el array para eliminar el usuario con el email dado
            $filteredSessions = array_filter($sessions, function ($session) use ($userEmail) {
                return $session['userEmail'] !== $userEmail;
            });
    
            // Comprobar si el número de sesiones ha cambiado después del filtrado
            if (count($filteredSessions) === count($sessions)) return;
    
            // Si el array filtrado está vacío, establece sessions como null
            if (empty($filteredSessions)) {
                $stay->sessions = null;
            } else {
                $sessions = array_values($filteredSessions); // reindexa el array para asegurar la integridad de los índices
                $stay->sessions = $sessions;
            }
    
            $stay->save();
            Log::info('deleteSession $sessions:'. json_encode($sessions));
            sendEventPusher(
                'private-stay-sessions-hotel.' . $stay->hotel_id, 
                'App\Events\SessionsStayEvent', 
                [ 'stayId' => $stay->id, 'session' => $sessions]
            );
            return $stay->sessions;
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    //guest 

    public function getGuestListWithNoti($stayId){
        try {
            $stay = Stay::select('id')->where('id', $stayId)->first();
            //lista de huespedes de una estancia con conteo de consultas pendientes
            $listGuests = $stay->guests()
            ->withCount(['queries as queryCount' => function ($query) use($stay){
                // Asumimos que 'attended' es un campo booleano y que '0' representa 'false'
                $query->where('stay_id', $stay->id)
                ->where('answered', '1')
                ->where('attended', '0');
            }])
            ->get();

            return $listGuests;
        } catch (\Exception $e) {
            return $e;
        }
    }

}
