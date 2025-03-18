<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\City;
use App\Models\Hotel;

class loadCityObjectidInHotelSeeder extends Seeder
{
    public function run(): void
    {
        // $hotels = Hotel::whereNotNull('latitude')->whereNotNull('longitude')->whereNull('zone_objectid')->count();
        // return;
        Hotel::whereNotNull('latitude')->whereNotNull('longitude')->whereNull('city_id')->chunk(100, function($hotels) {
            foreach ($hotels as $hotel) {
                $lat = $hotel->latitude;
                $long = $hotel->longitude;
                if ($lat && $long) {
                    $city = null;
                    $response = \Http::get('https://th-main-pyutils.bravebeach-0bf6ac58.westeurope.azurecontainerapps.io/geodata/get_objectid/', [
                        'lat' => $lat,
                        'lon' => $long,
                    ]);
                    if ($response->successful()) {
                        $objectId = $response->collect()->get('OBJECTID', 1);
                        $city = City::find($objectId)?->name ?? null;
                        $hotel->zone = $city;
                        $hotel->city_id = $objectId;
                        $hotel->save();
                    }
                }
            }
        });
    }
}
|