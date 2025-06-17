<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Apis\ApiReviewServices;
use App\Services\NotificationDiscordService;
use App\Models\Hotel;
use App\Jobs\UpdateReviewsJob;
use App\Jobs\UpdateTranslateReviewsJob;
use Illuminate\Support\Facades\Bus;

class UpdateReviewsCommand extends Command
{
    protected $signature = 'app:update-reviews-command';

    protected $description = 'Command description';

    public function __construct(ApiReviewServices $apiReviewService, NotificationDiscordService $notificationDiscordService)
    {
        parent::__construct();
        $this->apiReviewService = $apiReviewService;
        $this->notificationDiscordService = $notificationDiscordService;
    }

    public function handle()
    {
        // Bus::chain([
        //     new UpdateReviewsJob($this->apiReviewService),
        //     new UpdateTranslateReviewsJob($this->apiReviewService)
        // ])->dispatch();
        UpdateReviewsJob::dispatchSync($this->apiReviewService, $this->notificationDiscordService);
        // UpdateTranslateReviewsJob::dispatch($this->apiReviewService);
    }
}