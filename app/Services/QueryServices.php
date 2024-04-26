<?php

namespace App\Services;

use App\Models\Query;
use App\Models\QuerySetting;
use App\Models\Stay;
use App\Http\Resources\QueryResource;
use App\Jobs\Queries\NotifyPendingQuery;
use App\Mail\Queries\NewFeedback;
use App\Mail\Queries\RequestReviewGuest;
use App\Models\Guest;
use App\Models\hotel;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\ChatGPTService;
use Illuminate\Support\Facades\Mail;


class QueryServices {

    protected $chatGPTService;
    public $settings;

    public function __construct(
        ChatGPTService $chatGPTService,
        QuerySettingsServices $_QuerySettingsServices
    )
    {
        $this->chatGPTService = $chatGPTService;
        $this->settings = $_QuerySettingsServices;
    }

    public function findByParams ($request) {
        try {
            $stayId = $request->stayId ?? null;
            $guestId = $request->guestId ?? null;
            $period = $request->period ?? null;
            $visited = $request->visited ?? 'null';

            $query = Query::where(function($query) use($stayId, $guestId, $period, $visited){
                if ($stayId) {
                    $query->where('stay_id', $stayId);
                }
                if ($guestId) {
                    $query->where('guest_id', $guestId);
                }
                if ($period) {
                    $query->where('period', $period);
                }
                if ($period) {
                    $query->where('period', $period);
                }
                if ($visited !== 'null') {
                    $query->where('visited', $visited);
                }
                
            });
            $model = $query->first();

            $data = new QueryResource($model);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateParams($id, array $params)
    {
        try {
            $query = Query::findOrFail($id);
            $query->update($params);
            return $query; 
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateParams');
        }
    }

    //obtener periodo actual para la consulta
    public function getCurrentPeriod ($hotel, $stayId) {
        if(!$stayId) return;
        try {
            // Log::info('getCurrentPeriod stayId:'.$stayId);
            $stay =  Stay::find($stayId);
            // Log::info('getCurrentPeriod stay-result'.$stay);
            $dayCheckin = $stay->check_in;
            $dayCheckout = $stay->check_out;
            $hourCheckin = $hotel->checkin ?? '16:00';

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

    public function saveResponse ($id, $request, $hotel) {
        try{
            $comment = $request->comment;
            $responseLang = 'es';
            if($comment){
                $response = $this->chatGPTService->translateQueryMessage($comment, $id);
                $comment = $response["translations"];
                $responseLang = $response["responseLang"];
            }
            
            $query = Query::find($id);
            $query->answered = true;
            $query->qualification = $request->qualification;
            $query->response_lang = $responseLang;
            $query->responded_at= now();
            $query->comment = $comment;
            $query->save();
            
            $settings = $this->settings->notifications($hotel->id);
            
            $periodUrl = $query->period;
            if($periodUrl == 'in-stay') $periodUrl = 'stay';
            $stay = Stay::find($request->stayId);
            $stay->pending_queries_seen = true;
            $stay->save();

            $guest = Guest::select('id','phone','email')->where('id',$query->guest_id)->first();
            //solicitud de reseña
            if($query->qualification == 'GOOD'){
                Mail::to($guest->email)->send(new RequestReviewGuest($hotel));    
                if($guest->phone){
                    $msg = 'solicitud de reseña';
                    sendSMS($guest->phone,$msg,$hotel->sender_for_sending_sms);
                }
                
            }

            $urlQuery = config('app.hoster_url')."tablero-hoster/estancias/consultas/".$periodUrl."?selected=".$stay->id;
            
            if($settings->notify_to_hoster['notify_when_guest_send_via_platform']){
                sendEventPusher('notify-send-query.' . $hotel->id, 'App\Events\NotifySendQueryEvent', 
                [
                    "urlQuery" => $urlQuery,
                    "title" => "Nuevo feedback",
                    "text" => "Tienes un nuevo feedback",
                ]
                );   
            }
            
            if($settings->notify_to_hoster['notify_when_guest_send_via_email']){
                $checkinFormat = Carbon::createFromFormat('Y-m-d', $stay->check_in)->format('d/m/Y');
                $checkoutFormat = Carbon::createFromFormat('Y-m-d', $stay->check_out)->format('d/m/Y');
                $dates = "$checkinFormat - $checkoutFormat";
                Mail::to($guest->email)->send(new NewFeedback($dates, $urlQuery, $hotel , 'new'));    
            }

            $via_platform = $settings->notify_to_hoster['notify_later_when_guest_send_via_platform'];
            $via_email = $settings->notify_to_hoster['notify_later_when_guest_send_via_email'];
            if($via_platform || $via_email){
                NotifyPendingQuery::dispatch($hotel->id, $stay->id, $via_platform, $via_email)->delay(now()->addMinutes(10));
            }

            return $query; 
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveResponse');
        }
    }

    
}