<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 3;
    public $timeout = 2400;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        \Log::info("Start JobTest");
        sleep(1800);
        \Log::info("End JobTest");
    }
}
