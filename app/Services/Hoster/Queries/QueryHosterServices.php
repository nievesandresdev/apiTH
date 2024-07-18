<?php
namespace App\Services\Hoster\Queries;

use App\Models\Guest;
use App\Models\Stay;
use App\Services\Hoster\Stay\StayHosterServices;
use Carbon\Carbon;

class QueryHosterServices {

    public $stayHosterServices;


    function __construct(
        StayHosterServices $_StayHosterServices
    )
    {
        $this->stayHosterServices = $_StayHosterServices;
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

    public function getPrestayStatus($guest, $stayId, $period, $stayCheckin, $guestAccess){
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
    }

    public function geStayStatus($guest, $stayId, $period, $stayCheckin, $stayCheckout, $guestAccess){
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
    }

    public function getPostStayStatus($guest, $stayId, $period, $stayCheckout, $guestAccess){
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
    }


    
}
