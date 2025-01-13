<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use COM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use App\Mail\FreetrialAboutToEnd;
use App\Mail\FreeTrialEnded;

use App\Services\MailService;

class FreeTrealCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freetrealcommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificacion de vincimiento del free treal';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        MailService $_mail_services,
    )
    {
        parent::__construct();
        $this->mail_services = $_mail_services; 
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $date_before = $now->copy()->addDays(1);
        $users_before = User::role('Associate')
                ->whereDate('trial_ends_at', $date_before)
                ->get();
                
        foreach ($users_before as $user) {
            $on_trial = $user->onTrial();
            $hotels = $user->hotel;
            $subscribed = false;
            foreach ($hotels as $hotel) {
                $subscribed = $user->subscribed($hotel['subscription_active']);
                if(!$subscribed){
                    break;
                }
            }
            if ($on_trial && !$subscribed) {
                $this->mail_services->sendEmail(new FreetrialAboutToEnd($user), $user->email);
            }
                        
        }

        $date_yesterday = $now->copy()->subDays(1);

        $users_yesterday = User::role('Associate')
                ->whereDate('trial_ends_at', $date_yesterday)
                ->get();
        
        foreach ($users_yesterday as $user) {
            $on_trial = $user->onTrial();
            $hotels = $user->hotel;
            $subscribed = false;
            foreach ($hotels as $hotel) {
                $subscribed = $user->subscribed($hotel['subscription_active']);
                if(!$subscribed){
                    break;
                }
            }        
            if (!$on_trial && !$subscribed) {
                $this->mail_services->sendEmail(new FreeTrialEnded($user), $user->email);
            }
                        
        }
                
        return 0;

    }
}