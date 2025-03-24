<?php

namespace Database\Seeders;

use App\Models\DossierData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DossierConsistencySeeder extends Seeder
{
    public function run()
    {
        $grouped = DB::table('dossiers')
            ->select('name', DB::raw('GROUP_CONCAT(type) as types'))
            ->groupBy('name')
            ->get();

        //Log::info($grouped);

        foreach ($grouped as $group) {
            $types = explode(',', $group->types);
            $hasA = in_array('A', $types);
            $hasB = in_array('B', $types);

            if ($hasA && !$hasB) {
                $dossierA = DB::table('dossiers')->where('name', $group->name)->where('type', 'A')->first();
                echo "→ Faltante tipo B para '{$group->name}', creando...";

                // Clonar dossier A a B
                $newId = DB::table('dossiers')->insertGetId([
                    'name' => $dossierA->name,
                    'type' => 'B',
                    'domain' => $dossierA->domain,
                    'status' => $dossierA->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Crear dossier_data para tipo B
                DossierData::create([
                    'dossier_id' => $newId,
                    'tab_number' => null,
                    'rooms' => 0,
                    'averagePrice' => 0,
                    'occupancyRate' => 0,
                    'reputationIncrease' => 0,
                    'pricePerNightIncrease' => 0,
                    'occupancyRateIncrease' => 0,
                    'openMonths' => 0,
                    'recurrenceIndex' => 0,
                    'pricePerRoomPerMonth' => 599.00,
                    'implementationPrice' => 900.00,
                    'investmentInHoster' => 0,
                    'benefit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            } elseif (!$hasA && $hasB) {
                $dossierB = DB::table('dossiers')->where('name', $group->name)->where('type', 'B')->first();
                echo "→ Faltante tipo A para '{$group->name}', creando...";

                // Clonar dossier B a A
                $newId = DB::table('dossiers')->insertGetId([
                    'name' => $dossierB->name,
                    'type' => 'A',
                    'domain' => $dossierB->domain,
                    'status' => $dossierB->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Crear dossier_data para tipo A
                DossierData::create([
                    'dossier_id' => $newId,
                    'tab_number' => null,
                    'rooms' => 0,
                    'averagePrice' => 0,
                    'occupancyRate' => 0,
                    'reputationIncrease' => 0,
                    'pricePerNightIncrease' => 0,
                    'occupancyRateIncrease' => 0,
                    'openMonths' => 0,
                    'recurrenceIndex' => 0,
                    'pricePerRoomPerMonth' => 17.99,
                    'implementationPrice' => 900.00,
                    'investmentInHoster' => 0,
                    'benefit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo " Dossiers sincronizados: todos tienen tipo A y B con su dossier_data.";
    }
}

