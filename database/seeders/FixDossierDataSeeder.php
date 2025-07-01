<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Dossier, DossierData};
use Illuminate\Support\Facades\DB;

class FixDossierDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando reparación de datos DossierData...');

        // Obtener todos los registros de DossierData con type o numeral NULL
        $dossierDataToFix = DossierData::whereNull('type')
            ->orWhereNull('numeral')
            ->with('dossier')
            ->get();

        $this->command->info("Encontrados {$dossierDataToFix->count()} registros para reparar");

        // Agrupar por dossier_id para asignar numerales secuenciales
        $groupedByDossier = $dossierDataToFix->groupBy('dossier_id');

        $fixed = 0;

        foreach ($groupedByDossier as $dossierId => $dossierDataRecords) {
            $dossier = Dossier::find($dossierId);

            if (!$dossier) {
                $this->command->warn("Dossier con ID {$dossierId} no encontrado. Saltando...");
                continue;
            }

            $this->command->info("Procesando Dossier ID: {$dossierId}, Type: {$dossier->type}, Domain: {$dossier->domain}");

            // Obtener el siguiente numeral disponible para este dossier
            $maxNumeral = DossierData::where('dossier_id', $dossierId)
                ->whereNotNull('numeral')
                ->max('numeral');

            $maxNumeral = $maxNumeral ? $maxNumeral : 0;

            $currentNumeral = $maxNumeral + 1;

            foreach ($dossierDataRecords as $dossierData) {
                $updates = [];

                // Asignar type si está NULL
                if (is_null($dossierData->type)) {
                    $updates['type'] = $dossier->type;
                }

                // Asignar numeral si está NULL
                if (is_null($dossierData->numeral)) {
                    $updates['numeral'] = $currentNumeral;
                    $currentNumeral++;
                }

                // Actualizar si hay cambios
                if (!empty($updates)) {
                    $dossierData->update($updates);
                    $fixed++;

                    $typeText = isset($updates['type']) ? $updates['type'] : 'no cambio';
                    $numeralText = isset($updates['numeral']) ? $updates['numeral'] : 'no cambio';
                    $this->command->line("  ✓ DossierData ID: {$dossierData->id} - Type: {$typeText}, Numeral: {$numeralText}");
                }
            }
        }

        $this->command->info("✅ Proceso completado. {$fixed} registros reparados.");

        // Mostrar resumen final
        $this->showSummary();
    }

    private function showSummary()
    {
        $this->command->info("\nRESUMEN FINAL:");

        $dossiers = Dossier::with('dossierData')->get();

        foreach ($dossiers as $dossier) {
            $dataCount = $dossier->dossierData->count();
            $numerals = $dossier->dossierData->pluck('numeral')->sort()->implode(', ');

            $this->command->line("Domain: {$dossier->domain} | Type: {$dossier->type} | Registros: {$dataCount} | Numerales: [{$numerals}]");
        }
    }
}
