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
        Bus::dispatch([
            // new UpdateReviewsJob($this->apiReviewService),
            new UpdateTranslateReviewsJob($this->apiReviewService)
        ]);
    }
}