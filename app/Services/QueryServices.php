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
use App\Mail\Queries\DissatisfiedGuest;
use Carbon\Carbon;
use App\Services\ChatGPTService;
use Illuminate\Support\Facades\Log;
use App\Services\MailService;
use App\Utils\Enums\EnumsLanguages;
use Illuminate\Support\Facades\Mail;


class QueryServices {

    protected $chatGPTService;
    public $settings;
    public $requestSettings;
    public $userServices;
    public $mailService;

    public function __construct(
        ChatGPTService $chatGPTService,
        QuerySettingsServices $_QuerySettingsServices,
        RequestSettingService $_RequestSettingService,
        UserServices $userServices,
        MailService $mailService
    )
    {
        $this->chatGPTService = $chatGPTService;
        $this->settings = $_QuerySettingsServices;
        $this->requestSettings = $_RequestSettingService;
        $this->userServices = $userServices;
        $this->mailService = $mailService;

    }

    public function findByParams ($request) {
        try {
            $stayId = $request->stayId ?? null;
            $guestId = $request->guestId ?? null;
            $period = $request->period ?? null;
            $answered = $request->answered ?? 'null';
            $visited = $request->visited ?? 'null';
            $disabled = $request->disabled ?? false;

            $query = Query::where(function($query) use($stayId, $guestId, $period, $visited, $disabled,$answered){
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
                if ($answered !== 'null') {
                    $query->where('answered', $answered);
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
        Log::info('saveResponse: ' . json_encode($request, JSON_PRETTY_PRINT));
        try{
            /**
             * guardar nuevo feedback
            */
            $query = Query::find($id);
            if ($query->answered) {
                $query->histories()->create([
                    'qualification'   => $query->qualification,
                    'comment'         => $query->comment,
                    'responded_at'    => $query->responded_at,
                    'response_lang'   => $query->response_lang,
                ]);
            }
            /**
             * traducir comentario
             */
            $comment = $request->comment;
            // $goodFeel = array('GOOD','VERYGOOD');
            // if (in_array($request->qualification, $goodFeel, true))  $comment = null;

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
            /**
             * guardar datos
             */
            $query->answered = true;
            $query->attended = false;
            $query->qualification = $request->qualification;
            $query->response_lang = $responseLang;
            $query->responded_at= now();
            $query->comment = $comment;
            $query->save();

            $periodUrl = $query->period;
            if($periodUrl == 'in-stay') $periodUrl = 'stay';
            $stay = Stay::find($request->stayId);
            $stay->pending_queries_seen = true;
            $stay->save();
            /**
             * solicitar reseña cuando sea propicio
             */
            $guest = Guest::select('id','phone','email','name')->where('id',$query->guest_id)->first();
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

            /**
             * notificaciones push, plataforma y email
             */
            $this->sendNotificationsToHoster($stay, $hotel, $periodUrl, $query, $guest);
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

    public function sendNotificationsToHoster ($stay, $hotel, $periodUrl, $query, $guest) {
        try{
            Log::info('query: ' . json_encode($query, JSON_PRETTY_PRINT));
            $settings = $this->settings->notifications($hotel->id);

            /**
             * query para traer user con notificacion de feedback push and email activa
            */

            $notificationFiltersNewFeedback = [
                'newFeedback' => true,
                'informDiscontent' => true,
            ];

            $specificChannels = ['push','email'];

            $usersByChannel = $this->userServices->getUsersHotelBasicData($hotel->id, $notificationFiltersNewFeedback, $specificChannels);

            $pushUsersFeedback = $usersByChannel['push'];
            $emailUserNewFeedback = $usersByChannel['email'];

            //email de descontento para el usuario
            if($query->qualification == 'WRONG' || $query->qualification == 'VERYWRONG'){
                $users = $usersByChannel['email'] ?? [];
                $usersWithInformDiscontent = collect($users)
                    ->filter(function ($user) {
                        // Decodificar el JSON de notifications
                        $notifications = json_decode($user['notifications'], true);
                        
                        // Verificar si email.informDiscontent es true
                        return isset($notifications['email']['informDiscontent']) 
                            && $notifications['email']['informDiscontent'] === true;
                    })
                ->values() // Reindexar el array
                ->all(); // Convertir de nuevo a array si es necesario

                $showNotify = true;
                //
                $respondedAt   = Carbon::createFromFormat('Y-m-d H:i:s', $query->responded_at, 'Europe/Madrid');
                $referenceDate = $query->period === 'post-stay'
                    ? Carbon::parse($stay->check_out, 'Europe/Madrid')
                    : Carbon::parse($stay->check_in,  'Europe/Madrid');   
                $daysDifference = max(0, $respondedAt->diffInDays($referenceDate));
                $dayLabel = $daysDifference === 1 ? 'día' : 'días';
                $beforeOrAfter = $respondedAt->lt($referenceDate) ? 'antes' : 'después';
                $periodLabel = $query->period === 'post-stay' ? 'check-out' : 'check-in';
                $textDate = "{$daysDifference} {$dayLabel} {$beforeOrAfter} del {$periodLabel}";
                //
                $saasUrl = config('app.hoster_url');
                $questionInStay = "¿Cómo calificarías tu nivel de satisfacción con tu estancia hasta ahora?";
                $questionPostStay = "¿Cómo ha sido tu experiencia con nosotros?";
                $data = [
                    "guestName" => "{$guest->name} {$guest->lastname}",
                    "checkin" => "2025-05-01",
                    "textDate" => $textDate,
                    "respondedAtFormatted" => $respondedAt->format('d/m/Y'),
                    "respondedHour" => $respondedAt->format('H:i'),
                    "responseLang" => $query->response_lang,
                    "question" => $query->period === 'post-stay' ? $questionPostStay : $questionInStay,
                    "comment" => $query->comment[$query->response_lang],
                    "langAbbr" => $query->response_lang,
                    "languageResponse" => EnumsLanguages::NAME[$query->response_lang],
                    "urlToStay" => null,
                    "guestEmail" => $guest->email,
                ];
                foreach ($usersWithInformDiscontent as $user) {
                    Log::info('user: ' . json_encode($user, JSON_PRETTY_PRINT));
                    $email = $user->email;
                    $urlToStay = "{$saasUrl}/estancias/{$stay->id}/feedback?g={$guest->id}&redirect=view&code={$user->login_code}";
                    $data['urlToStay'] = $urlToStay;
                    $this->mailService->sendEmail(new DissatisfiedGuest($hotel, $showNotify, $data), $email);
                }
            }
            return [
                'pushUsersFeedback' => $pushUsersFeedback,
                'emailUserNewFeedback' => $emailUserNewFeedback,
            ];


            /**
             * notificar al hoster del nuevo feedback
             */
            //noticacion via push y plataforma
            // Log::info('$hotel->id '. $hotel->id);
            // Log::info('$stay->id '. $stay->id);
            // Log::info('$guest->id '. $guest->id);
            if ($pushUsersFeedback->isNotEmpty()) {
                $pushUsersFeedback->each(function ($user) use ($hotel, $guest, $stay) {
                    sendEventPusher('notify-send-query.' . $hotel->id, 'App\Events\NotifySendQueryEvent',
                    [
                        "stayId" => $stay->id,
                        "guestId" => $guest->id,
                        'user_id' => $user->id,
                        "title" => "Nuevo feedback",
                        "text" => "Tienes un nuevo feedback",
                        "concept" => "new",
                        "countPendingQueries" => 1
                    ]
                    );
                });
            }


            //noticacion via email
            //trae los ususarios y sus roles asociados al hotel en cuestion
            //$queryUsers = $this->userServices->getUsersHotelBasicData($hotel->id);

/*
            $notificacionFilterFeedbackPending10 = [
                'pendingFeedback10' => true,
            ]; */

            /* $queryUsers = $this->userServices->getUsersHotelBasicData($hotel->id, $notificationFiltersNewFeedback);
            $queryUsersFeedback10 = $this->userServices->getUsersHotelBasicData($hotel->id, $notificacionFilterFeedbackPending10);
            Log::info('Feedback users: ' . json_encode([
                'newFeedback' => $queryUsers,
                'newFeedbackuser10min' => $queryUsersFeedback10
            ], JSON_PRETTY_PRINT)); */





            // Extraer los roles de usuario a notificar para un nuevo mensaje
            /* $rolesToNotifyNewFeddback = collect($settings->email_notify_new_feedback_to);
            $getUsersRoleNewFeedback = $queryUsers->filter(function ($user) use ($rolesToNotifyNewFeddback) {
                return $rolesToNotifyNewFeddback->contains($user['role']);
            }); */


            $checkinFormat = Carbon::createFromFormat('Y-m-d', $stay->check_in)->format('d/m/Y');
            $checkoutFormat = Carbon::createFromFormat('Y-m-d', $stay->check_out)->format('d/m/Y');
            $dates = "$checkinFormat - $checkoutFormat";

            $urlQuery = config('app.hoster_url')."estancias/$stay->id/feedback?g=$guest->id";
            /* if ($getUsersRoleNewFeedback->isNotEmpty()) {/estancias/90/feedback?g=161
                $getUsersRoleNewFeedback->each(function ($user) use ($dates, $urlQuery, $hotel, $query, $guest, $stay) {
                    Mail::to($user['email'])->send(new NewFeedback($dates, $urlQuery, $hotel ,$query,$guest,$stay, 'new'));
                });
            } */

            // Verificar si hay usuarios
            if ($emailUserNewFeedback->isNotEmpty()) {

                // Enviar correo usuarios con newchat true
                $emailUserNewFeedback->each(function ($user) use ($dates, $urlQuery, $hotel, $query, $guest, $stay) {
                    $email = $user->email;
                    //$this->mailService->sendEmail(new ChatEmail($urlChat, 'new'), $email);
                    $this->mailService->sendEmail(new NewFeedback($dates, $urlQuery, $hotel ,$query,$guest,$stay, 'new'), $email);
                    //Mail::to($email)->send(new NewFeedback($dates, $urlQuery, $hotel ,$query,$guest,$stay, 'new'));
                });
            }
            /**
             * notificar al hoster de feedback pendiente
             */
            // Extraer los roles de usuario a notificar para chat pendiente luego de 10 min
            //$queryUsersFeedback10 = $this->userServices->getUsersHotelBasicData($hotel->id, $notificacionFilterFeedbackPending10);
           /*  $rolesToNotifyPendingFeedback = collect($settings->email_notify_pending_feedback_to);
            $getUsersRolePendingFeedback = $queryUsers->filter(function ($user) use ($rolesToNotifyPendingFeedback) {
                return $rolesToNotifyPendingFeedback->contains($user['role']);
            }); */
            // DB::table('jobs')->where('payload', 'like', '%NotifyPendingQuery'.$guest->id.'%')->delete();
            //if ($getUsersRolePendingFeedback->isNotEmpty()) {
                //job para notificar en un lapso de 10min
                /* NotifyPendingQuery::dispatch(
                    'NotifyPendingQuery'.$guest->id,
                    $dates, $urlQuery, $hotel,
                    $query, $guest, $stay,
                    $queryUsersFeedback10
                )
                ->delay(now()->addMinutes(10)); */
            //}
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getResponses');
        }
    }
}
