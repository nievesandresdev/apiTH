<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2400;
    public $tries = 1;

    public $hotel;
    public $apiReviewService;


    public function __construct($hotel, $apiReviewService)
    {
        $this->hotel = $hotel;
        $this->apiReviewService = $apiReviewService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->apiReviewService->syncReviews($this->hotel);
    }
}
