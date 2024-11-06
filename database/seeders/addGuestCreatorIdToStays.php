<?php

namespace Database\Seeders;

use App\Models\Stay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class addGuestCreatorIdToStays extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stays = Stay::all();
        
        foreach ($stays as $stay) {
            $firstGuest = $stay->guests()->first();
            
            if ($firstGuest) { // Verificar si existe un huésped asociado
                $stay->update(['guest_id' => $firstGuest->id]);
            }
        }
    }
}
