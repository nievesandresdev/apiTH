<?php

namespace App\Services;

class CloneFacilityService
{
    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD) {
        $result = $this->cloneFacility($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
        return $result;
    }

    public function cloneFacility($HOTEL_ID_PARENT, $HOTEL_ID_CHILD) {
        return 'cloneFacility';
    }
}
