<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chain;

class UpdateDuplicateSubdomainsSeeder extends Seeder
{
    public function run()
    {
        $subdomains = Chain::select('id', 'subdomain')
            ->orderBy('subdomain')
            ->get()
            ->groupBy('subdomain')
            ->filter(function ($items) {
                return $items->count() > 1;
            });

        foreach ($subdomains as $subdomain => $chains) {
            $count = 1;

            foreach ($chains as $chain) {
                // Si no es el primer elemento, modificamos el subdominio y lo registramos
                if ($count > 1) {
                    $newSubdomain = $subdomain . $count;
                    // Actualizar el subdominio
                    $chain->subdomain = $newSubdomain;
                    $chain->save();
                }
                $count++;
            }
        }
    }
}
