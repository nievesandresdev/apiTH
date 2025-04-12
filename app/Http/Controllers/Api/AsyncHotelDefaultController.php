<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloneFacilityService;

class AsyncHotelDefaultController extends Controller
{
    protected $cloneFacilityService;

    public function __construct(CloneFacilityService $cloneFacilityService)
    {
        $this->cloneFacilityService = $cloneFacilityService;
    }



    public function handle()
    {
        $HOTEL_ID_PARENT = config('app.dossier_hotel_id_parent');
        $HOTEL_ID_CHILD = config('app.dossier_hotel_id_child');
        $result = $this->cloneFacilityService->handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
        return $result;
    }
}
