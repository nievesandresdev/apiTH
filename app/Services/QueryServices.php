<?php

namespace App\Services;

use App\Models\Query;
use App\Models\QuerySetting;
use App\Models\Stay;
use App\Http\Resources\QueryResource;
use App\Jobs\Queries\NotifyPendingQuery;
use App\Mail\Queries\NewFeedback;
use App\Mail\Queries\RequestReviewGuest;
use App\Models\{Guest, User};
use App\Models\hotel;
use App\Services\Hoster\Users\{UserServices};
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use App\Jobs\Queries\FeedbackMsg;
use Carbon\Carbon;
use App\Services\ChatGPTService;
use Illuminate\Support\Facades\Mail;


class QueryServices {

    protected $chatGPTService;
    public $settings;
    public $requestSettings;
    public $userServices;

    public function __construct(
        ChatGPTService $chatGPTService,
        QuerySettingsServices $_QuerySettingsServices,
        RequestSettingService $_RequestSettingService,
        UserServices $userServices
    )
    {
        $this->chatGPTService = $chatGPTService;
        $this->settings = $_QuerySettingsServices;
        $this->requestSettings = $_RequestSettingService;
        $this->userServices = $userServices;

    }

    public function findByParams ($request) {
        try {
            $stayId = $request->stayId ?? null;
            $guestId = $request->guestId ?? null;
            $period = $request->period ?? null;
            $visited = $request->visited ?? 'null';
            $disabled = $request->disabled ?? false;

            $query = Query::where(function($query) use($stayId, $guestId, $period, $visited, $disabled){
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
                if ($disabled) {
                    $query->where('disabled', true);
                }else{
                    $query->where('disabled', false);
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
    public function getCurrentPeriod($hotel, $stayId) {
        try {
            $stay =  Stay::find($stayId);
            $dayCheckin = $stay->check_in;
            $dayCheckout = $stay->check_out;
            $hourCheckin = $hotel->checkin ?? '16:00';

            // Crear objeto Carbon para check-in
            $checkinDateTimeString = $dayCheckin . ' ' . $hourCheckin;
            $checkinDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkinDateTimeString);

            // período in-stay
            // $inStayStart = (clone $checkinDateTime)->addDay()->setTime(5, 0);
            $hideStart = Carbon::createFromFormat('Y-m-d', $dayCheckout);

             // período post-stay
            $postStayStart = Carbon::createFromFormat('Y-m-d H:i', $dayCheckout . ' 05:00');
            $postStayEnd = (clone $hideStart)->addDays(10);

            //fecha actual
            $now = Carbon::now();
            if ($now->lessThan($checkinDateTime)) {
                return 'pre-stay';
            }
            // if ($now->greaterThanOrEqualTo($inStayStart) && $now->lessThan($hideStart)) {
            if ($now->greaterThan($checkinDateTime) && $now->lessThan($hideStart)) {
                return 'in-stay';
            }
            if ($now->greaterThanOrEqualTo($postStayStart) && $now->lessThanOrEqualTo($postStayEnd)) {
                return 'post-stay';
            }
             // Nueva condición para verificar si han pasado más de 10 días después del checkout
            if ($now->greaterThan($postStayEnd)) {
                //return 'invalid-stay';
                return 'post-stay';
            }
            return null;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function firstOrCreate ($stayId, $guestId, $period, $disabled = false) {
        try{
            return Query::firstOrCreate([
                'stay_id' => $stayId,
                'guest_id' => $guestId,
                'period' => $period,
                'disabled' => $disabled,
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
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getResponses');
        }
    }

    public function saveResponse ($id, $request, $hotel) {
        try{
            
            $settingsPermissions = $this->settings->getAll($hotel->id);
            /**
             * trae los ususarios y sus roles asociados al hotel en cuestion
             */
                $queryUsers = $this->userServices->getUsersHotelBasicData($hotel->id);

                // Extraer los roles de email_notify_new_feedback_to
                $rolesToNotify = collect($settingsPermissions['email_notify_new_feedback_to']);

                // Filtrar los usuarios que tengan uno de esos roles
                $filteredUsers = $queryUsers->filter(function ($user) use ($rolesToNotify) {
                    return $rolesToNotify->contains($user['role']);
                });

            /** fin traer user asociados y permisos */

            $query = Query::find($id);
            if ($query->answered) {
                $query->histories()->create([
                    'qualification'   => $query->qualification,
                    'comment'         => $query->comment,
                    'responded_at'    => $query->responded_at,
                    'response_lang'   => $query->response_lang,
                ]);
            }

            $comment = $request->comment;
            $originalComment = $request->comment;
            $responseLang = 'es';
            if($comment){
                $response = $this->chatGPTService->translateQueryMessage($comment, $id);
                $comment = $response["translations"];
                $responseLang = $response["responseLang"];
                if($responseLang == 'und'){
                    $responseLang = 'es';
                    $comment = ['es' => $originalComment, 'en' => $originalComment];
                }
            }

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

            $guest = Guest::select('id','phone','email','name')->where('id',$query->guest_id)->first();

            //solicitud de reseña
            $requestSettings = $this->requestSettings->getAll($hotel->id);
            $condition1 = $requestSettings->request_to == "positive queries" && $query->qualification == "GOOD" && $query->period == 'post-stay';
            $condition2 = $requestSettings->request_to == "positive, normal and not answered queries" && ($query->qualification == "GOOD" || $query->qualification == "NORMAL") && $query->period == 'post-stay';
            if($condition1 || $condition2){
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

            //if($settings->notify_to_hoster['notify_when_guest_send_via_email']){
                $checkinFormat = Carbon::createFromFormat('Y-m-d', $stay->check_in)->format('d/m/Y');
                $checkoutFormat = Carbon::createFromFormat('Y-m-d', $stay->check_out)->format('d/m/Y');
                $dates = "$checkinFormat - $checkoutFormat";

                if ($filteredUsers->isNotEmpty()) {
                    $filteredUsers->each(function ($user) use ($dates, $urlQuery, $hotel, $query, $guest, $stay) {
                        Mail::to($user['email'])->send(new NewFeedback($dates, $urlQuery, $hotel ,$query,$guest,$stay, 'new'));

                        FeedbackMsg::dispatch($user['email'], $dates, $urlQuery, $hotel, $query, $guest, $stay)
                                                ->delay(now()->addMinutes(10));
                    });
                }
            //}

            $via_platform = $settings->notify_to_hoster['notify_later_when_guest_send_via_platform'];
            $via_email = $settings->notify_to_hoster['notify_later_when_guest_send_via_email'];
            if($via_platform || $via_email){
                NotifyPendingQuery::dispatch($hotel->id, $stay->id, $via_platform, $via_email)->delay(now()->addMinutes(10));
            }
            /* return [
                'dates' => $dates,
                'urlQuery' => $urlQuery,
                'hotel' => $hotel,
                'query' => $query,
                'guest' => $guest,
                'stay' => $stay,
                'users' => $queryUsers,
                'settingsPermissions' => $settingsPermissions,
                'filteredUsers' => $filteredUsers->all(),
            ]; */
            return $query;
        } catch (\Exception $e) {
            //\Log::error('Error Mail Feedback new',$e->getMessage());
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveResponse');
        }
    }


}
