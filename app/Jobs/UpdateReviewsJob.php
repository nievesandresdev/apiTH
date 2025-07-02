<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Hotel;


class UpdateReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 2400;
    public $tries = 1;

    public $apiReviewService;
    public $hotelService;


    public function __construct($apiReviewService, $hotelService)
    {
        $this->apiReviewService = $apiReviewService;
        $this->hotelService = $hotelService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info("Start UpdateReviewsCommand");
        // $codeHotels = ['f9d8c71a516af822a8b44fbe4c116f50', 'c4687e2a827e461c4037d6681c4732c5', 'c010f0b234e8868e4e08e30bfca76fb1', '67f06c5e1e9ed065e6ec5dc8960bdd8b', '7cb2e8a36a8953dce7cb229d55ba3395', '2a62888b35a87b6c90eed3757cfb7928', '7bd448c4bd8f88453c9dd4cd5d15093c', '67acad79061d0816dd3d6cb9af58cd1f', '17160715040133464864', '8163422725375425318' , '2d8d89951b907e073407a7dddaebd31c', 'f6852088ddd15f1e4ec7e77d2b11e151', '8cd9612630f6a0828fd3b43e4f7441d8', '886a9f9edb12bdd4ac1d58f0283b6855', 'f7b378dbe2465d163bfa0ab7ebed6e0a', '69c9f24cf520f3689bf4a26368f4c4f2'];
        // $codeHotels = ['8163422725375425318'];
        $codeHotels = $this->hotelService->getHotelsSubscriptionActive();

        var_dump($codeHotels);


        // foreach ($codeHotels as $codeHotel) {
        //     $hotel = Hotel::where('code', $codeHotel)->first();
        //     if ($hotel) {
        //         \Log::info("[Hotel: " . $hotel->name . " - Code: " . $hotel->code . " - id: " . $hotel->id . "]");
        //         $this->apiReviewService->updateReviews($hotel);
        //         \Log::info("[End UpdateReviewsJob]");
        //     }
        // }
        // \Log::info("End UpdateReviewsCommand");
        

    }
}
