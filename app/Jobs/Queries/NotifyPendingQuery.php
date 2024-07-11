<?php
namespace App\Jobs\Queries;

use App\Mail\Queries\NewFeedback;
use App\Models\ChatMessage;
use App\Models\hotel;
use App\Models\Query;
use App\Models\Stay;
use App\Services\QuerySettingsServices;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyPendingQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $hotelId;
    public $stayId;
    public $viaPlatform;
    public $viaEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($hotelId, $stayId, $viaPlatform ,$viaEmail)
    {
        $this->hotelId = $hotelId;
        $this->stayId = $stayId;
        $this->viaPlatform = $viaPlatform;
        $this->viaEmail = $viaEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $hotel = hotel::find($this->hotelId);
        Log::info('JOB $this->viaPlatform'.$this->viaPlatform);
        $query = Query::where('stay_id',$this->stayId)
                    ->where('answered',1)
                    ->where('attended',0)
                    ->first();
        Log::info('JOB query '.$query);
        $stay = Stay::find($this->stayId);

        $periodUrl = $query->period;
        if($periodUrl == 'in-stay') $periodUrl = 'stay';

        $urlQuery = config('app.hoster_url')."tablero-hoster/estancias/consultas/".$periodUrl."?selected=".$stay->id;
        Log::info('JOB $urlQuery '.$urlQuery);
        Log::info('JOB $this->viaPlatform '.$this->viaPlatform);
        if($query && $this->viaPlatform){
            sendEventPusher('notify-send-query.' . $hotel->id, 'App\Events\NotifySendQueryEvent',
            [
                "urlQuery" => $urlQuery,
                "title" => "Feedback pendiente",
                "text" => "Tienes un feedback pendiente",
            ]
            );
        }
        Log::info('JOB $this->viaEmail '.$this->viaEmail);
        if($query && $this->viaEmail){
            Log::info('JOB entro '.$this->viaEmail);
            $checkinFormat = Carbon::createFromFormat('Y-m-d', $stay->check_in)->format('d/m/Y');
            Log::info('JOB entro '.$checkinFormat);
            $checkoutFormat = Carbon::createFromFormat('Y-m-d', $stay->check_out)->format('d/m/Y');
            Log::info('JOB entro '.$checkoutFormat);
            $dates = "$checkinFormat - $checkoutFormat";
            //Mail::to("andresdreamerf@gmail.com")->send(new NewFeedback($dates, $urlQuery, $hotel, 'pending'));
        }


    }
}
