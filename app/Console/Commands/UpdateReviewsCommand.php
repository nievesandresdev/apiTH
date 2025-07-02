<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Apis\ApiReviewServices;
use App\Models\Hotel;
use App\Jobs\UpdateReviewsJob;
use App\Jobs\UpdateTranslateReviewsJob;
use Illuminate\Support\Facades\Bus;
use App\Services\HotelService;

class UpdateReviewsCommand extends Command
{
    protected $signature = 'app:update-reviews-command';

    protected $description = 'Command description';

    public function __construct(ApiReviewServices $apiReviewService, HotelService $hotelService)
    {
        parent::__construct();
        $this->apiReviewService = $apiReviewService;
        $this->hotelService = $hotelService;
    }

    public function handle()
    {
        // Bus::chain([
        //     new UpdateReviewsJob($this->apiReviewService),
        //     new UpdateTranslateReviewsJob($this->apiReviewService)
        // ])->dispatch();
        UpdateReviewsJob::dispatch($this->apiReviewService, $this->hotelService);
        // UpdateTranslateReviewsJob::dispatchSync($this->apiReviewService);
    }
}