<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;

class AssignOrderToFacilityHosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotelCollection = Hotel::all();
        foreach ($hotelCollection as $hotelModel) {
            $faciltiesCollection = $hotelModel->facilities()->where('select', 1)->get();
            foreach ($faciltiesCollection as $keyFacility => $facilityModel) {
                $facilityModel->update(['order' => $keyFacility + 1]);
            }
        }
    }
}
