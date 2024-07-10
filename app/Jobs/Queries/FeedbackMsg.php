<?php

namespace App\Jobs\Queries;

use App\Mail\Queries\NewFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class FeedbackMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userEmail;
    protected $dates;
    protected $urlQuery;
    protected $hotel;
    protected $query;
    protected $guest;
    protected $stay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userEmail, $dates, $urlQuery, $hotel, $query, $guest, $stay)
    {
        $this->userEmail = $userEmail;
        $this->dates = $dates;
        $this->urlQuery = $urlQuery;
        $this->hotel = $hotel;
        $this->query = $query;
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
        Mail::to($this->userEmail)->send(new NewFeedback(
            $this->dates,
            $this->urlQuery,
            $this->hotel,
            $this->query,
            $this->guest,
            $this->stay,
            'pending'
        ));
    }
}
