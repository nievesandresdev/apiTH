<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Apis\ApiReviewServices;
use App\Models\Hotel;
use App\Jobs\UpdateReviewsJob;
class UpdateReviewsCommand extends Command
{
    protected $signature = 'app:update-reviews-command';

    protected $description = 'Command description';

    public function __construct(ApiReviewServices $apiReviewService)
    {
        parent::__construct();
        $this->apiReviewService = $apiReviewService;
    }

    public function handle()
    {
        \Log::info("Start UpdateReviewsCommand");
        $codeHotels = ['f9d8c71a516af822a8b44fbe4c116f50', 'c4687e2a827e461c4037d6681c4732c5', 'c010f0b234e8868e4e08e30bfca76fb1', '67f06c5e1e9ed065e6ec5dc8960bdd8b', '7cb2e8a36a8953dce7cb229d55ba3395', '2a62888b35a87b6c90eed3757cfb7928', '7bd448c4bd8f88453c9dd4cd5d15093c', '67acad79061d0816dd3d6cb9af58cd1f', '17160715040133464864', '8163422725375425318 '];

        foreach ($codeHotels as $codeHotel) {
            $hotel = Hotel::where('code', $codeHotel)->first();
            \Log::info("Hotel: " . $hotel->name);
            if ($hotel) {
                UpdateReviewsJob::dispatchSync($hotel, $this->apiReviewService);
                // $this->apiReviewService->syncReviews($hotel);
            }
        }
        \Log::info("End UpdateReviewsCommand");
    }
}