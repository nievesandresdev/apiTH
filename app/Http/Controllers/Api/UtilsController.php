<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\NotifyUnreadMsg;
use App\Http\Controllers\Controller;
use App\Jobs\Chat\NofityPendingChat;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\hotel;
use App\Models\NoteGuest;
use App\Models\Query;
use App\Models\RequestSettingsHistory;
use App\Models\Stay;
use App\Models\StayAccess;
use App\Services\ChatService;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Services\Hoster\RequestReviews\RequestReviewsSettingsServices;
use App\Services\Hoster\Stay\StaySettingsServices;
use App\Services\Hoster\Users\UserServices;
use App\Services\Hoster\UtilsHosterServices;
use App\Services\QuerySettingsServices;
use App\Services\UtilityService;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class UtilsController extends Controller
{   

    public $settings;
    public $staySettings;
    public $userServices;
    public $chatService;
    public $chatSettingsServices;
    public  $requestReviewsSettingsServices;
    public  $utilsHosterServices;
    public  $utilityService;

    function __construct(
        QuerySettingsServices $_QuerySettingsServices,
        UserServices $userServices,
        ChatService $_ChatService,
        StaySettingsServices $_StaySettingsServices,
        ChatSettingsServices $_ChatSettingsServices,
        RequestReviewsSettingsServices $_RequestReviewsSettingsServices,
        UtilsHosterServices $_UtilsHosterServices,
        UtilityService $_UtilityService
    )
    {
        $this->settings = $_QuerySettingsServices;
        $this->userServices = $userServices;
        $this->chatService = $_ChatService;
        $this->staySettings = $_StaySettingsServices;
        $this->chatSettingsServices = $_ChatSettingsServices;
        $this->requestReviewsSettingsServices = $_RequestReviewsSettingsServices;
        $this->utilsHosterServices = $_UtilsHosterServices;
        $this->utilityService = $_UtilityService;
    }

    public function authPusher(Request $request)
    {
        $user = auth()->user(); // O tu lógica de autenticación personalizada
        
        // if ($user) {
            $pusher = new Pusher(
                config('services.pusher.key'), 
                config('services.pusher.secret'), 
                config('services.pusher.id'), 
                [
                    'cluster' => config('services.pusher.cluster'),
                    'useTLS' => true
                ]
            );
    
            $authResponse = $pusher->socket_auth($request->input('channel_name'), $request->input('socket_id'));
            return response($authResponse, 200)->header('Content-Type', 'application/json');
        // } else {
        //     return response('Forbidden', 403);
        // }
    }

    public function testTemplateEmail()
    {
        
        $hotel = hotel::find(191);
        $stay = Stay::find(460);
        $chainSubdomain = $hotel->subdomain;

        $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
        $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
        $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);

        $webappLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain);
        $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');

        return view('Mails.guest.InviteToInWebapp', [
            'hotel' => $hotel,
            'formatCheckin' => $formatCheckin,
            'formatCheckout' => $formatCheckout,
            'crosselling' => $crosselling,
            'webappLink' => $webappLink,
            'webappChatLink' => $webappChatLink,
        ]);
    }

    
    public function test()
    {
        // //cuando presione en muy buena
        // $caritaMuyBuena = true;
        // //cuando presione cualquiera de las otras
        // // $caritaMuyBuena = false;

        // $gooFeel = $caritaMuyBuena ? "&fill=VERYGOOD" : '';
        // $chainSubdomain = "nobuhotelsevillatex";
        // $subdomainHotel = "nobuhotelsevillatex";
        // $guestId = 322;
        // $stayId = 460;
        // return buildUrlWebApp($chainSubdomain, $subdomainHotel,'inbox',"e={$stayId}&g={$guestId}{$gooFeel}");



        
        Log::info('inicia send-post-stay-emails');
        $startTime = Carbon::now()->subHours(72)->startOfHour();
        $endTime = Carbon::now()->subHours(24)->startOfHour();
        Log::info('$startTime'.$startTime);
        Log::info('$endTime'.$endTime);
        // Filtra las estancias dentro de este rango de tiempo
        $stays = Stay::select('id','hotel_id','check_out')->whereHas('hotel')
            ->whereBetween('check_out', [$startTime->toDateString(), $endTime->toDateString()])
            ->with([
                'queries' => function($query) {
                    $query->select('id', 'stay_id','guest_id','answered','qualification')->where('period', 'post-stay');
                },
                'queries.guest' => function($query) {
                    $query->select('id', 'name','email');
                }
            ])
            ->get();
        Log::info('$stays'.$stays);
        foreach($stays as $stay){

            
            $checkOutDate = Carbon::parse($stay->check_out);
            $Time = $stay->hotel->checkout ?? '05:00'; 
            $Hour = explode(':', $Time)[0]; 
            $Minute = explode(':', $Time)[1];
            $checkOutDateTime = $checkOutDate->copy()->setTime($Hour, $Minute);

            $now = Carbon::now();
            $hoursDifference = $now->diffInHours($checkOutDateTime);
            Log::info('$stay'.$stay);
            Log::info('$hoursDifference'.$hoursDifference);
            foreach($stay->queries as $query){
                
                Log::info('$query '.json_encode($query));
                $queries_url = url('consultas?e='.$stay->id.'&lang='.$query->guest->lang_web.'&g='.$query->guest->id);
                $link = includeSubdomainInUrlHuesped($queries_url, $stay->hotel);
                Log::info('$link'.$link);
                return $hoursDifference;
                if(intval($hoursDifference) == 49){
                    Log::info('answered '.boolval($query->answered));
                    if(!boolval($query->answered)){
                        Log::info('enviado a '.$query->guest->email);
                        // Mail::to($query->guest->email)->send(new InsistencePostStayResponse($link, $stay->hotel));
                    }
                    
                    $requestSettings = $this->requestSettings->getAll($stay->hotel->id);
                    $arr = json_decode($requestSettings->request_to);
                    $inArrayCondition = in_array('NORMAL',$arr);
                    Log::info('$requestSettings->request_to '.$requestSettings->request_to);
                    $goodArr = ['GOOD','VERYGOOD'];
                    $normalArr = ['GOOD','VERYGOOD','NORMAL'];

                    // Log::info('inArrayCondition '.json_encode($inArrayCondition));
                    // Log::info('include goodArr '.json_encode(in_array($query->qualification,$goodArr)));
                    // Log::info('include goodArr '.json_encode(in_array($query->qualification,$normalArr)));

                    $condition1 = (!$inArrayCondition) && in_array($query->qualification,$goodArr) && boolval($query->answered);
                    $condition2 = ($inArrayCondition) && in_array($query->qualification,$normalArr) && boolval($query->answered);
                    $condition3 = ($inArrayCondition) && !boolval($query->answered);

                    Log::info('$condition1 '.json_encode($condition1));
                    Log::info('$condition2 '.json_encode($condition2));
                    Log::info('$condition3 '.json_encode($condition3));

                    if($condition1 || $condition2 || $condition3){
                        Mail::to($query->guest->email)->send(new RequestReviewGuest($link, $stay->hotel));        
                    }
                }
            }
        }
    }



}
