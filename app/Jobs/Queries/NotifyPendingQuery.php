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

    public $nameJob;
    public $dates;
    public $urlQuery;
    public $hotel;
    public $query;
    public $guest;
    public $stay;
    public $usersList;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($nameJob, $dates, $urlQuery, $hotel, $query, $guest, $stay, $usersList)
    {
        $this->dates = $dates;
        $this->urlQuery = $urlQuery;
        $this->hotel = $hotel;
        $this->query = $query;
        $this->guest = $guest;
        $this->stay = $stay;
        $this->usersList = $usersList;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('NotifyPendingQuery');
        $query = Query::where('id',$this->query->id)
                    ->where('attended',0)
                    ->first();
        Log::info('$query '.json_encode($query));
        if($query){
            sendEventPusher('notify-send-query.' . $this->hotel->id, 'App\Events\NotifySendQueryEvent',
                [
                    "stayId" => $this->stay->id,
                    "guestId" => $this->guest->id,
                    "title" => "Feedback pendiente",
                    "text" => "Tienes un feedback pendiente",
                    "countPendingQueries" => 1
                ]
            );
            Log::info('ENVIADA NOTIFICACION PUSH');
            $this->usersList->each(function ($user) {
                Log::info('ENVIADO EMAIL A '.$user['email']);
                Mail::to($user['email'])->send(new NewFeedback($this->dates, $this->urlQuery, $this->hotel ,$this->query,$this->guest,$this->stay, 'pending'));
            });
        }
       

    }
}
