<?php
namespace App\Jobs\Stay;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailGuest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $chainSubdomain;
    protected $hotel;
    protected $guest;
    protected $stay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, $chainSubdomain, $hotel, $guest, $stay = null)
    {
        $this->type = $type;
        $this->chainSubdomain = $chainSubdomain;
        $this->hotel = $hotel;
        $this->guest = $guest;
        $this->stay = $stay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('inicio SendEmailGuest');
        // Aquí invocas el método que envía el email
        Log::info('type '.json_encode($this->type));
        Log::info('chainSubdomain '.json_encode($this->chainSubdomain));
        Log::info('hotel '.json_encode($this->hotel));
        Log::info('guest '.json_encode($this->guest));
        Log::info('stay '.json_encode($this->stay));

        // $this->guestWelcomeEmail('welcome', $chainSubdomain, $hotel, $guest, $stay);
        app('App\Services\StayService')->guestWelcomeEmail($this->type, $this->chainSubdomain, $this->hotel, $this->guest, $this->stay);
    }
}
