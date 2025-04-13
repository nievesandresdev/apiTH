<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CloneFacilityService;

class AsyncHotelDefaultCommand extends Command
{
    protected $signature = 'async:hotel-default';
    protected $description = 'Async Hotel Default Command';
    
    public function __construct(
        CloneFacilityService $cloneFacilityService
    )
    {
        parent::__construct();
    }

    public function handle () {
        $HOTEL_ID_PARENT = config('app.DOSSIER_HOTEL_ID');
        $HOTEL_ID_CHILD = config('app.dossier_hotel_id_child');
        $this->cloneFacilityService->handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
    }
    
}
