<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\IntegrationPms;

class IntegrationPmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();

        foreach ($hotels as $hotel) {
            IntegrationPms::create([
                'hotel_id' => $hotel->id,
                'name_pms' => 'SiteMinder',
                'url_pms' => null,
                'with_url' => 0,
                'email_pms' => null,
                'password_pms' => null,
            ]);
        }
    }
}
