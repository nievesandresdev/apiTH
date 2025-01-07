<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Hotel;

class ChangeTypeHotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Hotel::all()->each(function ($hotel) {
            $hotel->type = strtolower($hotel->type);
            $hotel->save();
        });   
    }
}
