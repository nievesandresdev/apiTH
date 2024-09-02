<?php
namespace App\Services\Hoster\Queries;

use App\Models\Chat;
use App\Models\Guest;
use App\Models\Query;
use App\Models\Stay;
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
    

    function __construct(
        StayHosterServices $_StayHosterServices,
        QueryServices $_QueryServices,
        StaySessionServices $_StaySessionServices
    )
    {
        $this->stayHosterServices = $_StayHosterServices;
        $this->queryService = $_QueryServices;
        $this->staySessionServices = $_StaySessionServices;
    }

    public function getFeedbackSummaryByGuest ($stayId, $guestId, $hotel) {
        try {
            $guestData = Guest::select('id','name','acronym','lang_web','color')
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
            
            return [
                'guest' => $guestData,
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
            $postStayQuery  = $this->getPostStayStatus($guestData, $stay->id, $period, $stay->check_out, $guestAccess);
            $postStayRequest = $this->getPostStayRequest($queryPostStay, $period);

            $timeLineData = [
                'pre-stay' => $preStayQuery,
                'in-stay' => $stayQuery,
                'post-stay' => $postStayQuery,
                'request' => $postStayRequest,
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
            ->where('guest_id', $guestId)
            ->where('stay_id', $stayId)
            ->orderBy('created_at', 'asc')
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

    public function getPostStayRequest($queryPostStay, $currentPeriod){

        $icon = "Pendiente";
        $answeredTime = null;
        $goodAnswers = ['GOOD','VERYGOOD'];
        $goodFeedback = $queryPostStay ? in_array($queryPostStay->qualification, $goodAnswers) : false;
        if($currentPeriod == 'post-stay' || $currentPeriod == 'invalid-stay'){
            if($goodFeedback && $queryPostStay->answered){
                $icon = "Solicitado";
                $answeredTime = $queryPostStay->responded_at;
            }
            if(!$goodFeedback && $queryPostStay && $queryPostStay->answered){
                $icon = "No solicitado";
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
    

}
