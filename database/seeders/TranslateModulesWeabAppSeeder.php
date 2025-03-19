<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Services\HotelService;
use App\Services\FacilityService;

class TranslateModulesWeabAppSeeder extends Seeder
{

    function __construct(
        HotelService $_HotelService,
        FacilityService $_FacilityService
    )
    {
        $this->hotelService = $_HotelService;
        $this->facilityService = $_FacilityService;
    }


    public function run(): void
    {
        $this->hotelService->translateAll();
        var_dump('traslated hotel finish');
        $this->facilityService->translateAll();
        var_dump('traslated facility finish');
        
    }
}
