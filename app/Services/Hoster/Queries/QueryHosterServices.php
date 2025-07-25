<?php
namespace App\Services\Hoster\Queries;

use App\Models\Chat;
use App\Models\Guest;
use App\Models\Query;
use App\Models\Stay;
use App\Services\Hoster\RequestReviews\RequestReviewsSettingsServices;
use App\Services\Hoster\Stay\StayHosterServices;
use App\Services\Hoster\Stay\StaySessionServices;
use App\Services\QueryServices;
use App\Utils\Enums\EnumsLanguages;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryHosterServices {

    public $stayHosterServices;
    public $queryService;
    public $chatHosterServices;
    public $staySessionServices;
    public $requestReviewsSettingsServices;

    function __construct(
        StayHosterServices $_StayHosterServices,
        QueryServices $_QueryServices,
        StaySessionServices $_StaySessionServices,
        RequestReviewsSettingsServices $_RequestReviewsSettingsServices
        
    )
    {
        $this->stayHosterServices = $_StayHosterServices;
        $this->queryService = $_QueryServices;
        $this->staySessionServices = $_StaySessionServices;
        $this->requestReviewsSettingsServices = $_RequestReviewsSettingsServices;
    }

    public function getFeedbackSummaryByGuest ($stayId, $guestId, $hotel) {
        try {
            $guestData = Guest::select('id','name','lastname','acronym','lang_web','color')
                        ->with([
                            'notes' => function($q) use($stayId){
                            $q->where('stay_id', $stayId);
                            }
                        ])
                        ->find($guestId);

            $dataDetail = $this->stayHosterServices->getdetailDateData($stayId, $hotel);
            //status de cada consulta
            $guestAccess = $guestData->stayAccesses()->where('stay_id',$stayId)->first();
            
            $preStayQuery = $this->getPrestayStatus($guestData, $stayId, $dataDetail['period'], $dataDetail['stayCheckin'], $guestAccess);
            $stayQuery  = $this->geStayStatus($guestData, $stayId, $dataDetail['period'], $dataDetail['stayCheckin'], $dataDetail['stayCheckout'], $guestAccess);
            $postStayQuery  = $this->getPostStayStatus($guestData, $stayId, $dataDetail['period'], $dataDetail['stayCheckout'], $guestAccess);

            $countStayTest = $guestData->stays()->where('trial',true)->where('hotel_id',$hotel->id)->count();
            return [
                'guest' => $guestData,
                'countStayTest' => $countStayTest,
                'queries' => [
                    'preStay' => $preStayQuery,
                    'stay' => $stayQuery,
                    'postStay' => $postStayQuery,
                ],
                'stay' => $dataDetail,
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getFeedbackTimelineByGuest ($guestId, $stay, $hotel) {
        try {
            
            $period = $this->queryService->getCurrentPeriod($hotel, $stay->id);

            $guestData = Guest::select('id')->where('id',$guestId)->first();
            $guestAccess = $guestData->stayAccesses()->where('stay_id',$stay->id)->first();

            $preStayQuery = $this->getPrestayStatus($guestData, $stay->id, $period, $stay->check_in, $guestAccess);
            $stayQuery  = $this->geStayStatus($guestData, $stay->id, $period, $stay->check_in, $stay->check_out, $guestAccess);
            $queryPostStay =  $guestData->queries()->where('stay_id',$stay->id)->where('period','post-stay')->orderBy('created_at','asc')->first();
            $queryInStay =  $guestData->queries()->where('stay_id',$stay->id)->where('period','in-stay')->orderBy('created_at','asc')->first();
            $postStayQuery  = $this->getPostStayStatus($guestData, $stay->id, $period, $stay->check_out, $guestAccess);
            $inStayRequest = $this->getInStayRequest($queryInStay, $hotel->id);
            $postStayRequest = $this->getPostStayRequest($queryPostStay, $hotel->id);

            $timeLineData = [
                'pre-stay' => $preStayQuery,
                'in-stay' => $stayQuery,
                'post-stay' => $postStayQuery,
                'request' => [
                    'in-stay' => $inStayRequest,
                    'post-stay' => $postStayRequest
                ],
                'guestAccess' => $guestAccess,
                'currentPeriod' => $period,
                'stay' => $stay,
            ];
            return $timeLineData;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getDataByGuest ($guestId, $stayId) {
        try {
            // histories
            $dataQueries = Query::with(['histories' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->with(['guest' => function ($query) {
                $query->select('id','email');
            }])
            ->where('guest_id', $guestId)
            ->where('stay_id', $stayId)
            ->orderByRaw("FIELD(period, 'pre-stay', 'in-stay', 'post-stay')")
            ->get();
        
            foreach ($dataQueries as $query) {
                if ($query->comment) {
                    $query['languages'] = $this->extractLanguages($query->comment);
                }
                foreach ($query->histories as $history) {
                    if ($history->comment) {
                        $history['languages'] = $this->extractLanguages($history->comment);
                    }
                }
            }
            
            return $dataQueries;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getPostStayRequest($queryPostStay, $hotelId){

        $icon = "Aún por determinar";
        $answeredTime = null;
        $goodAnswers = ['GOOD','VERYGOOD'];
        if($queryPostStay && $queryPostStay->answered){
            $arrActiveWhenResponding = $this->requestReviewsSettingsServices->fieldAtTheMoment('request_to', $queryPostStay->responded_at, $hotelId);
            $goodAnswers = json_decode($arrActiveWhenResponding);
            $goodFeedback = $queryPostStay ? in_array($queryPostStay->qualification, $goodAnswers) : false;
            
            if($goodFeedback){
                $icon = "Reseña solicitada";
                $answeredTime = $queryPostStay->responded_at;
            }else{
                $icon = "Reseña no solicitada";
            }
        }
        return [
            "icon" => $icon,
            "answeredTime" => $answeredTime
        ];
    }

    public function getInStayRequest($queryInStay, $hotelId){

        $icon = "Aún por determinar";
        $answeredTime = null;
        $goodAnswers = ['GOOD','VERYGOOD'];
        if($queryInStay && $queryInStay->answered){
            $goodFeedback = $queryInStay ? in_array($queryInStay->qualification, $goodAnswers) : false;
            $activeWhenResponding = $this->requestReviewsSettingsServices->fieldAtTheMoment('in_stay_activate', $queryInStay->responded_at, $hotelId);
            if(!$activeWhenResponding){
                $icon = "Solicitud desactivada";
            }else{
                if($goodFeedback){
                    $icon = "Reseña solicitada";
                    $answeredTime = $queryInStay->responded_at;
                }else{
                    $icon = "Reseña no solicitada";
                }
            }
        }
        return [
            "icon" => $icon,
            "answeredTime" => $answeredTime
        ];
    }

    
    public function getPrestayStatus($guest, $stayId, $period, $stayCheckin, $guestAccess){
        
        try {
            $queryPreStay =  $guest->queries()
                    ->where('stay_id',$stayId)
                    ->where('period','pre-stay')
                    ->first();

            $iconPreStay = "No enviado";
            $shippingTime = null;
            $answeredTime = null;
            $feeling = null;
            //si existe la query
            if($queryPreStay){
                //para todos los casos
                if($queryPreStay->disabled){
                    $iconPreStay = "Desactivado";
                }else{
                    //para todos los casos
                    if($queryPreStay->answered){
                        $iconPreStay = "Respondido";
                        $answeredTime = $queryPreStay->responded_at;
                        $shippingTime = $queryPreStay->created_at;
                    }
                    //cuando estamos aun en pre-stay
                    if(!$queryPreStay->answered && $period == 'pre-stay'){
                        $iconPreStay = "Esperando respuesta";
                        $shippingTime = $queryPreStay->created_at;
                    }
                    //cuando ya paso pre-stay
                    if(!$queryPreStay->answered && $period !== 'pre-stay'){
                        $iconPreStay = "No respondido";
                        $shippingTime = $queryPreStay->created_at;
                    }
                }
            }

            //cuando no existe la query
            if(!$queryPreStay){
                ////cuando ya paso pre-stay
                // if($period !== 'pre-stay'){
                    //validar si el acceso fue posterior a pre-stay
                    $checkInDate = Carbon::parse($stayCheckin); // Parsea la fecha de check-in
                    $checkInTime = currentHotel()->checkin ?? '16:00'; // Usa '22:00' como predeterminado si currentHotel()->checkin es null
                    $checkInHour = explode(':', $checkInTime)[0]; // Extrae la hora
                    $checkInMinute = explode(':', $checkInTime)[1]; // Extrae los minutos
                    // Establece la hora de check-in al día de check-in
                    $checkInDateTime = $checkInDate->copy()->setTime($checkInHour, $checkInMinute);
                    $accessCreatedAt = Carbon::parse($guestAccess->created_at);
                    if ($accessCreatedAt->greaterThan($checkInDateTime)) {
                        $iconPreStay = "No enviado";
                    }else{
                        $iconPreStay = "Error";
                    }
                // }
            }


            return [
                "icon" => $iconPreStay,
                "shippingTime" => $shippingTime,
                "answeredTime" => $answeredTime,
                "feeling" => $feeling,
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function geStayStatus($guest, $stayId, $period, $stayCheckin, $stayCheckout, $guestAccess){
        try {
            $queryStay =  $guest->queries()
                    ->where('stay_id',$stayId)
                    ->where('period','in-stay')
                    ->first();
            $checkIn = Carbon::parse($stayCheckin);

            $checkOutDate = Carbon::parse($stayCheckout);
            // Establece la hora de check-in al día de check-in
            $checkOutDateTime = $checkOutDate->copy()->setTime('05', '00');
            $checkInAtFiveAm = $checkIn->copy()->addDay()->startOfDay()->addHours(5);

            //copy para el point respuesta
            $iconStay = "Envío programado";
            $shippingTime = $checkInAtFiveAm;
            $answeredTime = null;
            $feeling = null;
            $accessCreatedAt = Carbon::parse($guestAccess->created_at);

            if($period !== 'pre-stay'){
                $iconStay = "Desactivado";
                $shippingTime = $guestAccess->created_at;
                if($queryStay){
                    if($queryStay->disabled){
                        $iconStay = "Desactivado";
                    }else if($period == 'post-stay' || $period == 'invalid-stay'){
                        if(!$queryStay->answered){
                            $iconStay = "No respondido";
                            $shippingTime = $queryStay->created_at;
                        }
                    }else if(!$queryStay->answered){
                        $iconStay = "Esperando respuesta";
                        $shippingTime = $queryStay->created_at;
                    }

                    if($queryStay->answered){
                        $iconStay = "Respondido";
                        $answeredTime = $queryStay->responded_at;
                        $feeling = $queryStay->qualification;
                    }

                }else{
                    if ($accessCreatedAt->greaterThan($checkOutDateTime)) {
                        $iconStay = "No enviado";
                    }else{
                        $iconStay = "Error";
                    }
                }
            }
            return [
                "icon" => $iconStay,
                "shippingTime" => $shippingTime,
                "answeredTime" => $answeredTime,
                "feeling" => $feeling
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getPostStayStatus($guest, $stayId, $period, $stayCheckout, $guestAccess){
        try {
            $queryPostStay =  $guest->queries()
                    ->where('stay_id',$stayId)
                    ->where('period','post-stay')
                    ->first();

            $checkOutDate = Carbon::parse($stayCheckout);
            // Establece la hora de check-in al día de check-in
            $checkOutDateTime = $checkOutDate->copy()->setTime('05', '00');

            //copy para el point respuesta
            $iconStay = "Envío programado";
            $shippingTime = $checkOutDateTime;
            $answeredTime = null;
            $feeling = null;
            $accessCreatedAt = Carbon::parse($guestAccess->created_at);

            if($period !== 'pre-stay' && $period !== 'in-stay'){
                $iconStay = "Esperando respuesta";
                $shippingTime = $guestAccess->created_at;

                if($period == 'invalid-stay'){
                    if($queryPostStay && !$queryPostStay->answered){
                        $iconStay = "No respondido";
                        $shippingTime = $queryPostStay->created_at;
                    }
                }

                if($queryPostStay && $queryPostStay->answered){
                    $iconStay = "Respondido";
                    $shippingTime = $queryPostStay->created_at;
                    $answeredTime = $queryPostStay->responded_at;
                    $feeling = $queryPostStay->qualification;
                }
            }
            return [
                "icon" => $iconStay,
                "shippingTime" => $shippingTime,
                "answeredTime" => $answeredTime,
                "feeling" => $feeling,
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getDetailByGuest($guestId, $stayId, $hotel){
        try {
            $stay = Stay::find($stayId);
            $guestList = $this->stayHosterServices->getGuestListWithNoti($stayId);

            $timeline = $this->getFeedbackTimelineByGuest($guestId, $stay, $hotel);

            $queryByGuest = $this->getDataByGuest($guestId, $stay->id);

            return [
                'guests' => $guestList,
                'timeline' => $timeline,
                'queryByGuest' => $queryByGuest
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    public function togglePendingState($queryId, $bool, $hotelId, $data) {
        try {
            $this->staySessionServices->updateActionOrcreateSession($data);
            // Log::info('togglePendingState');
            $query = Query::findOrFail($queryId);
            $query->attended = $bool;
            $query->save();
            //evento para actualizar lista de estancias en front
            sendEventPusher('private-update-stay-list-hotel.' . $hotelId, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
            sendEventPusher('private-noti-hotel.' . $hotelId, 'App\Events\NotifyStayHotelEvent',
                [
                    'showLoadPage' => false,
                    'pendingCountQueries' => $this->pendingCountByStay($query->stay_id),
                    'stayId' => $query->stay_id,
                    'hotel_id' => $hotelId,
                ]
            );
            return $query;
        } catch (\Exception $e) {
            return $e;
        }
    }

    private function extractLanguages($comment)
    {
        $languages = [];
        foreach ($comment as $languageCode => $commentText) {
            $languageName = EnumsLanguages::NAME[$languageCode] ?? 'Desconocido';
            $languages[] = [
                'name' => $languageName,
                'code' => $languageCode,
            ];
        }
        return collect($languages);
    }

    public function countPendingByHotel($hotelId){
        try{
            $count = DB::table('queries')
            ->join('stays', 'stays.id', '=', 'queries.stay_id')
            ->select('stays.id as StayId','stays.hotel_id', 'queries.id', 'queries.answered', 'queries.attended')
            ->where('answered', 1)->where('attended', 0)
            ->where('hotel_id', $hotelId)->count();

            return $count;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function pendingCountByStay($stayId){
        try{
            $count = Query::where('stay_id',$stayId)->where('answered', 1)->where('attended', 0)->count();
            return $count;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getGeneralReport($hotel, $request)
    {
        $periodType = $request->periodType ?? 'monthly';
        try {
            $periodOptions = $this->generatePeriodOptions($hotel, $periodType);
            $periodOptions = array_reverse($periodOptions);
            //
            $item  = $periodOptions[0];
            $value = $item['value']; 
            $json = str_replace("'", '"', $value);
            $data = json_decode($json);
            // 
            $from = $request->from ?? $data->from;
            $to   = $request->to ?? $data->to; 
            
            $periodsToSearch = $request->periodsToSearch ?? ['in-stay','post-stay'];
            $sort = $request->sort ?? 'recent';
            $stats = $this->getStats($from, $to, $hotel, $periodsToSearch, $sort);
            
            return [
                'periodOptions' => $periodOptions,
                'from' => $from,
                'to' => $to,
                'stats' => $stats,
                'hotelId' => $hotel->id,
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

    protected function generatePeriodOptions($hotel, string $periodType = 'monthly')
    {
        $created = $hotel->created_at->copy()->startOfDay();
        $now     = Carbon::now()->endOfDay();
        $options = [];

        if ($periodType === 'weekly') {
            // 1) Primer periodo: created → fin de semana
            $firstEnd  = $created->copy()->endOfWeek();
            $options[] = $this->makeOption($created, min($firstEnd, $now));

            // 2) Semanas completas intermedias
            $cursor = $firstEnd->copy()->addDay()->startOfDay();
            $weekStartCurrent = $now->copy()->startOfWeek();
            while ($cursor->lt($weekStartCurrent)) {
                $from = $cursor->copy();
                $to   = $cursor->copy()->endOfWeek();
                $options[] = $this->makeOption($from, $to);
                $cursor->addWeek();
            }

            // 3) Último periodo: inicio semana actual → hoy
            if ($weekStartCurrent->lte($now)) {
                $options[] = $this->makeOption($weekStartCurrent, $now);
            }
        } else {
            // 1) Primer periodo: created → fin de mes
            $firstEnd  = $created->copy()->endOfMonth();
            $options[] = $this->makeOption($created, min($firstEnd, $now));

            // 2) Meses completos intermedios
            $cursor = $firstEnd->copy()->addDay()->startOfDay();          // día 1 del mes siguiente
            $monthStartCurrent = $now->copy()->startOfMonth();
            while ($cursor->lt($monthStartCurrent)) {
                $from = $cursor->copy();
                $to   = $cursor->copy()->endOfMonth();
                $options[] = $this->makeOption($from, $to);
                $cursor->addMonth();
            }

            // 3) Último periodo: 1º día mes actual → hoy
            if ($monthStartCurrent->lte($now)) {
                $options[] = $this->makeOption($monthStartCurrent, $now);
            }
        }

        return $options;
    }

    protected function makeOption(Carbon $from, Carbon $to): array
    {
        return [
            'value' => "{'from': '{$from->toDateString()}', 'to': '{$to->toDateString()}'}",
            'label' => $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y'),
        ];
    }

    protected function getStats($from, $to, $hotel, $periodsToSearch, $sort){

        $quals = ['VERYGOOD','GOOD','NORMAL','WRONG','VERYWRONG'];

        $qs = Query::join('stays','queries.stay_id','stays.id')
                ->join('guests','queries.guest_id','guests.id')
                ->where('stays.hotel_id', $hotel->id)
                ->where('queries.answered', 1)
                ->whereIn('queries.period', $periodsToSearch)
                // filtro por intervalo de fechas (solo fecha)
                ->whereDate('queries.responded_at','>=',$from)
                ->whereDate('queries.responded_at','<=',$to)
                ->select(
                    'queries.period','queries.guest_id','queries.answered','queries.qualification','queries.comment','queries.responded_at','queries.response_lang',
                    'stays.hotel_id','stays.check_in','stays.check_out','stays.id as stayId',
                    'guests.name','guests.lastname'
                )
                // orden dinámico:
                ->when($sort == 'recent' || $sort == 'old', function($q) use ($sort) {
                    $dir = $sort === 'recent' ? 'desc' : 'asc';
                    return $q->orderBy('queries.responded_at', $dir);
                })

                // orden por QUALITATIVE:
                ->when($sort == 'good2bad' || $sort == 'bad2good', function($q) use ($sort, $quals) {
                    $list = $sort === 'good2bad'
                        ? $quals
                        : array_reverse($quals);
                    // convierte a "'VERYGOOD','GOOD',…"
                    $fieldList = implode(',', array_map(fn($v) => "'{$v}'", $list));
                    // MySQL / MariaDB: FIELD(col, …) devuelve la posición en la lista
                    return $q->orderByRaw("FIELD(queries.qualification, {$fieldList})");
                })

            ->get();

            
            // Función auxiliar para procesar un sub-conjunto
            $makeStats = function($collection) use($quals){
                $total = $collection->count() ?: 1; // evitar división por cero
                return collect($quals)->map(function($q) use($collection,$total){
                    $cnt = $collection->where('qualification',$q)->count();
                    return [
                        'qualification' => $q,
                        'count'         => $cnt,
                        'percent'       => round($cnt / $total * 100, 1),
                    ];
                });
            };

            $qs->each(function($query) {
                $query->languages =  $query->comment ? $this->extractLanguages($query->comment) : [];
                $query->guestFullName = $query->name . ' ' . $query->lastname;
            });
            // 4) Construyes los tres módulos
            return  [
                'total'   => $qs->count(),
                'comments_count'=> $qs->whereNotNull('comment')->count(),
                'breakdown'=> $makeStats($qs),
                'queries' => $qs,
            ];

    }


}
