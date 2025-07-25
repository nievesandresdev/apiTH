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
    public $staySessionServices;

    function __construct(
        QueryServices $_QueryServices,
        UtilsHosterServices $_UtilsHosterServices,
        StaySessionServices $_StaySessionServices
    )
    {
        $this->queryService = $_QueryServices;
        $this->utilsServices = $_UtilsHosterServices;
        $this->staySessionServices = $_StaySessionServices;
    }

    // 'stays.pending_queries_seen',
    // DB::raw("(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.answered = 1 AND queries.qualification = 'GOOD') as good_queries_count"),
    // DB::raw('(SELECT COUNT(*) FROM stay_accesses as sacc WHERE sacc.stay_id = stays.id) as accesses_count'),
    // DB::raw('(SELECT COUNT(*) FROM note_guests as ng WHERE ng.stay_id = stays.id) as guests_notes_count'),
    public function getAllByHotel($hotel, $filters, $offset = 0, $limit = 10) {
        try {

            $now = Carbon::now()->format('Y-m-d H:i');
            $limit = $filters['limit'] ?? $limit;
            $offset = $filters['offset'] ?? $offset;
            $query = Stay::with([
                    'chats:id,stay_id,pending',
                    'chats.messages:by,chat_id,status',
                    'guests:acronym,color,lang_web,complete_checkin_data,id',
                ])
                ->select([
                    'stays.id',
                    'stays.updated_at',
                    'stays.room',
                    'stays.number_guests',
                    'stays.check_out',
                    'stays.check_in',
                    'stays.trial',
                    'stays.is_demo',
                    DB::raw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.attended = 0 AND queries.answered = 1) as pending_queries_count'),
                    DB::raw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.answered = 1) as answered_queries_count'),
                    DB::raw('(SELECT MAX(pending) FROM chats WHERE chats.stay_id = stays.id) as has_pending_chats'),
                    DB::raw('(SELECT COUNT(*) FROM chats WHERE chats.stay_id = stays.id) as has_chats'),
                    DB::raw("CASE
                        WHEN '$now' < DATE_FORMAT(stays.check_in, CONCAT('%Y-%m-%d ', COALESCE((SELECT checkin FROM hotels WHERE hotels.id = stays.hotel_id), '16:00'))) THEN 'pre-stay'
                        WHEN '$now' >= DATE_FORMAT(stays.check_in, CONCAT('%Y-%m-%d ', COALESCE((SELECT checkin FROM hotels WHERE hotels.id = stays.hotel_id), '16:00'))) AND '$now' < stays.check_out THEN 'in-stay'
                        WHEN '$now' >= stays.check_out AND '$now' THEN 'post-stay'
                        END as period")
                ])
                ->where('hotel_id', $hotel->id)
                ->where('is_demo', 0);

            if (!empty($filters['search'])) {
                $query->whereHas('guests', function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%');
                });
            }

            $allStaysOnlySearch = (clone $query)->get();

            $periodCondition = !empty($filters['periods']);
            if ($periodCondition) {
                $query->havingRaw("period IN ('" . implode("','", $filters['periods']) . "')");
            }

            $pendingCondition = isset($filters['pendings']) && $filters['pendings'] == 'pending';
            if ($pendingCondition) {
                $query->where(function($q) {
                    $q->whereRaw('(SELECT COUNT(*) FROM queries WHERE queries.stay_id = stays.id AND queries.attended = 0 AND queries.answered = 1) > 0')
                      ->orWhereRaw('(SELECT MAX(pending) FROM chats WHERE chats.stay_id = stays.id) > 0');
                });
            }

            // $totalCount = (clone $query)->count();
            $allStays = (clone $query)->get();
            $countStayTest = $allStays->where('trial', 1)->count();
            $totalCount = count($allStays);


            $stays = $query->orderByRaw('
                CASE
                    WHEN has_pending_chats = 1 OR pending_queries_count > 0 THEN 0
                    WHEN has_chats > 0 THEN 1
                    WHEN answered_queries_count > 0 THEN 2
                    ELSE 3
                END ASC,
                stays.updated_at DESC,
                stays.id DESC
            ')
            ->offset($offset)
            ->limit($limit)
            ->get();
            // ->paginate($limit, ['*'], 'page', $page);
            // WHEN stays.room IS NULL OR stays.room = "" AND CURDATE() BETWEEN stays.check_in AND stays.check_out THEN 2

            // Counts for all, each period, and pending
            $totalValidCount = $allStays->where('period','!=','post-stay')->count();

            $countsByPeriod = $allStays->groupBy('period')->mapWithKeys(function ($items, $period) {
                return [$period => $items->count()];
            });

            //conteos general
            $countsGeneralByPeriod = $allStaysOnlySearch->groupBy('period')->mapWithKeys(function ($items, $period) {
                return [$period => $items->count()];
            });

            $pendingCountsByPeriod = $allStaysOnlySearch->reduce(function ($carry, $stay) {
                if ($stay->pending_queries_count > 0 || $stay->has_pending_chats > 0) {
                    if (!isset($carry[$stay->period])) {
                        $carry[$stay->period] = 0;
                    }
                    $carry[$stay->period]++;
                }
                return $carry;
            }, []);


            $stays->each(function ($stay) use ($hotel) {
                if(!$hotel->chat_service_enabled){
                    $stay->has_pending_chats = 0;
                }
                // Verificamos si al menos uno de sus huéspedes tiene complete_checkin_data = 1
                $atLeastOneCheckin = $stay->guests->contains(fn($guest) => $guest->complete_checkin_data == 1);
                // Agregamos la propiedad al modelo (no se guarda en DB, sino en la instancia)
                $stay->has_complete_checkin_data = $atLeastOneCheckin;
            });

            return [
                'stays' => $stays,
                'total_count' => $totalCount,
                'total_valid_count' => $totalValidCount,
                'counts_by_period' => $countsByPeriod,
                'counts_general_by_period' => $countsGeneralByPeriod,
                'pending_counts_by_period' => $pendingCountsByPeriod,
                'count_stay_test' => $countStayTest,
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
            $dataStays = $this->getAllByHotel($hotel, [], 0, 2000);

            $checkinToday = 0;
            $checkoutToday = 0;
            $totalGuests = 0;
            $countsByPeriod = ['pre-stay' => 0,'in-stay' => 0,'post-stay' => 0];
            $guestsByPeriod = ['pre-stay' => 0,'in-stay' => 0,'post-stay' => 0];
            $allLangs = getAllLanguages();
            $langsTotal = [];
            $percentageLangs = [];
            $arrayLangs = [];
            foreach($allLangs as $lang){
                $langsTotal[$lang] = 0;
                $percentageLangs[$lang] = 0;
                array_push($arrayLangs, $lang);
            }

            foreach($dataStays['stays'] as $stay){
                //today data
                $today == $stay->check_in ? $checkinToday++:'';
                $today == $stay->check_out ? $checkoutToday++:'';
                //guests counter
                $guestsByPeriod[$stay->period] += count($stay->guests);
                $totalGuests += count($stay->guests);
                foreach($stay->guests as $guest){
                    $langsTotal[strtolower($guest->lang_web)] += 1;
                    // echo "lang: ".strtolower($guest->lang_web)."\n";
                }
                //stays
                $countsByPeriod[$stay->period] += 1;
            }

            
            foreach($arrayLangs as $lang){
                if($langsTotal[$lang] > 0){
                    $percentageLangs[$lang] = round(($langsTotal[$lang]/$totalGuests)*100);
                }
            }


            // Asegurar que existen las claves
            $percentageLangs['es'] = $percentageLangs['es'] ?? 0;
            $percentageLangs['en'] = $percentageLangs['en'] ?? 0;

            // Separar los porcentajes deseados
            $es = $percentageLangs['es'];
            $en = $percentageLangs['en'];

            // Calcular el resto (otros idiomas)
            $othersTotal = 0;
            foreach ($percentageLangs as $key => $value) {
                if ($key !== 'es' && $key !== 'en') {
                    $othersTotal += $value;
                }
            }

            // Crear nuevo array ordenado
            $sortedPercentageLangs = [
                'es' => $es,
                'en' => $en,
                'others' => $othersTotal,
            ];


            return [
                'today' => $todayDay,
                'month' => $month,
                'checkinToday' => $checkinToday,
                'checkoutToday' => $checkoutToday,
                'countsByPeriod' => $countsByPeriod,
                'guestsByPeriod' => $guestsByPeriod,
                'totalGuests' => $totalGuests,
                'langsTotal' => $langsTotal,
                'percentageLangs' => $sortedPercentageLangs
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

            $guests = $stay->guests()->select('guests.id','guests.name','guests.lastname','guests.lang_web','guests.acronym','guests.color')->get();

            $url_stay_icon = 'https://ui-avatars.com/api/?name=ES&color=fff&background=34A98F';
            $optionsListNote = [
                ['img' => $url_stay_icon, 'label' => 'Estancia', 'value' => 'STAY']
            ];
            foreach ($guests as $key => $g) {
                $url_g_icon = "https://ui-avatars.com/api/?name=$g->acronym&color=fff&background=$g->color";
                array_push($optionsListNote, ['img' => $url_g_icon, 'label' => $g->name.' '.$g->lastname, 'value' => $g->id]);
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
                // "sessions" => $stay->sessions,
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
            $this->staySessionServices->updateActionOrcreateSession($data);
            $stay = Stay::find($stayId);
            $stay->room = $data->room ?? null;
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

    public function getDefaultGuestIdAndSessions($stayId) {
        try {
            $stay = Stay::with(['guests' => function($query){
                $query->select('guests.id as guestId','guests.complete_checkin_data')->get();
            }])->select('sessions','id')
                    ->where('id',$stayId)
                    ->first();
            return $stay;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function deleteTestStays($hotelId) {
        try {
            // $delete = Stay::where('hotel_id',$hotelId)
            //         ->where('trial',1)
            //         ->delete();


            $delete = Stay::where('hotel_id', $hotelId)
            ->where('trial', 1)
            ->get()
            ->each(function ($stay) {
                // Eliminamos registros relacionados en cada tabla
                DB::table('stay_accesses')->where('stay_id', $stay->id)->delete();
                DB::table('queries')->where('stay_id', $stay->id)->delete();
                DB::table('chats')->where('stay_id', $stay->id)->delete();
                DB::table('guest_stay')->where('stay_id', $stay->id)->delete();
                DB::table('note_guests')->where('stay_id', $stay->id)->delete();
                DB::table('note_stays')->where('stay_id', $stay->id)->delete();

                // Finalmente, se elimina la estancia
                $stay->delete();
            });
            sendEventPusher('private-update-stay-list-hotel.' . $hotelId, 'App\Events\UpdateStayListEvent', []);
            return $delete;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById($stayId) {
        return Stay::find($stayId);
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
