<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloneFacilityService;
use App\Services\Hoster\CloneHotelServices;
use App\Models\Hotel;

class AsyncHotelDefaultController extends Controller
{
    protected $cloneFacilityService;
    protected $cloneHotelServices;

    public function __construct(
        CloneFacilityService $cloneFacilityService,
        CloneHotelServices $cloneHotelServices
    )
    {
        $this->cloneFacilityService = $cloneFacilityService;
        $this->cloneHotelServices = $cloneHotelServices;
    }

    public function handle()
    {
        $HOTEL_ID_PARENT = 277;
        $HOTEL_ID_CHILD = 281;
        // $this->cloneFacilityService->handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
        $result = $this->cloneHotelServices->CopyCustomization(277, 281, 60);
        return $result;
    }

    public function getIdsCloned() {
        $HOTEL_ID_PARENT = config('app.DOSSIER_HOTEL_ID');
        $hotel = Hotel::whereNotNull('parent_id')->first();
        $HOTEL_ID_CHILD = $hotel ? $hotel->parent_id : null;
        return response()->json([
            'hotel_id_parent' => $HOTEL_ID_PARENT,
            'hotel_id_child' => $HOTEL_ID_CHILD,
        ]);
    }
    
}
