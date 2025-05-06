<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\JobTest;

class JobTestCommand extends Command
{

    protected $signature = 'app:job-test-command';

    protected $description = 'Command description';

    public function handle()
    {
        \Log::info("Start jobTestCommand");
        JobTest::dispatch();
        \Log::info("End jobTestCommand");
    }
}
