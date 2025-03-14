<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Services\HotelService;

class TranslateModulesWeabAppSeeder extends Seeder
{

    function __construct(
        HotelService $_HotelService
    )
    {
        $this->hotelService = $_HotelService;
    }


    public function run(): void
    {
        $this->hotelService->translateAll();
    }
}
