<?php

namespace App\Console\Commands;

use App\Mail\Queries\InsistencePostStayResponse;
use App\Mail\Queries\RequestReviewGuest;
use App\Models\Stay;
use App\Services\RequestSettingService;
use Illuminate\Console\Command;
use App\Services\StayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPostStayEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-post-stay-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $stayService;
    protected $requestSettings;
    /**
     * Execute the console command.
     */

     public function __construct(StayService $_StayServices, RequestSettingService $_RequestSettingService)
     {
         parent::__construct(); // Llama al constructor del padre
         $this->stayService = $_StayServices;
         $this->requestSettings = $_RequestSettingService;
     }

    public function handle()
    {
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
            $checkoutDateTime = $this->stayService->getCheckoutDateTime($stay->id);
            $now = Carbon::now();
            $hoursDifference = $now->diffInHours($checkoutDateTime);
            Log::info('$stay'.$stay);
            Log::info('$hoursDifference'.$hoursDifference);
            foreach($stay->queries as $query){
                Log::info('$query '.json_encode($query));
                $queries_url = url('consultas?e='.$stay->id.'&lang='.$query->guest->lang_web.'&g='.$query->guest->id);
                $link = includeSubdomainInUrlHuesped($queries_url, $stay->hotel);
                Log::info('$link'.$link);
                if(intval($hoursDifference) == 49){
                    Log::info('answered '.boolval($query->answered));
                    if(!boolval($query->answered)){
                        Log::info('enviado a '.$query->guest->email);
                        Mail::to($query->guest->email)->send(new InsistencePostStayResponse($link, $stay->hotel));
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
