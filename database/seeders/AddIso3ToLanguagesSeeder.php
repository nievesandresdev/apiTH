<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddIso3ToLanguagesSeeder extends Seeder
{
    public function run()
    {
        // Mapeo de 'abbreviation' => 'iso3' (Código de país).
        // NOTA: Los códigos elegidos son orientativos; ajústalos según tus necesidades.
        //       Por ejemplo, Inglés puede asociarse a 'GBR' (Reino Unido) o 'USA' (Estados Unidos), etc.
        $languagesIso = [
            // Activos
            ['abbreviation' => 'es',    'iso3' => 'ESP'], // Español => España
            ['abbreviation' => 'en',    'iso3' => 'GBR'], // Inglés => Reino Unido (o 'USA')
            ['abbreviation' => 'fr',    'iso3' => 'FRA'], // Francés => Francia
            ['abbreviation' => 'it',    'iso3' => 'ITA'], // Italiano => Italia
            ['abbreviation' => 'de',    'iso3' => 'DEU'], // Alemán => Alemania
            ['abbreviation' => 'pt',    'iso3' => 'PRT'], // Portugués => Portugal

            // Inactivos
            ['abbreviation' => 'bs',    'iso3' => 'BIH'], // Bosnio => Bosnia y Herzegovina
            ['abbreviation' => 'zh',    'iso3' => 'CHN'], // Chino => China
            ['abbreviation' => 'sl',    'iso3' => 'SVN'], // Esloveno => Eslovenia
            ['abbreviation' => 'no',    'iso3' => 'NOR'], // Noruego => Noruega
            ['abbreviation' => 'bg',    'iso3' => 'BGR'], // Búlgaro => Bulgaria
            ['abbreviation' => 'ko',    'iso3' => 'KOR'], // Coreano => Corea (del Sur)
            ['abbreviation' => 'hi',    'iso3' => 'IND'], // Hindi => India
            ['abbreviation' => 'pl',    'iso3' => 'POL'], // Polaco => Polonia
            ['abbreviation' => 'sr',    'iso3' => 'SRB'], // Serbio => Serbia
            ['abbreviation' => 'nl',    'iso3' => 'NLD'], // Holandés => Países Bajos
            ['abbreviation' => 'sq',    'iso3' => 'ALB'], // Albanés => Albania
            ['abbreviation' => 'cs',    'iso3' => 'CZE'], // Checo => Chequia
            ['abbreviation' => 'hr',    'iso3' => 'HRV'], // Croata => Croacia
            ['abbreviation' => 'sk',    'iso3' => 'SVK'], // Eslovaco => Eslovaquia
            ['abbreviation' => 'el',    'iso3' => 'GRC'], // Griego => Grecia
            ['abbreviation' => 'hu',    'iso3' => 'HUN'], // Húngaro => Hungría
            ['abbreviation' => 'ja',    'iso3' => 'JPN'], // Japonés => Japón
            ['abbreviation' => 'ro',    'iso3' => 'ROU'], // Rumano => Rumanía
            ['abbreviation' => 'sv',    'iso3' => 'SWE'], // Sueco => Suecia
            ['abbreviation' => 'tr',    'iso3' => 'TUR'], // Turco => Turquía

            // Nuevos
            // Para lenguas regionales de España (Euskera, Valenciano, etc.), 
            // se asocia por conveniencia 'ESP' (España) — ajústalo si necesitas otra lógica.
            ['abbreviation' => 'eu',    'iso3' => 'ESP'], // Euskera
            ['abbreviation' => 'caval','iso3' => 'ESP'], // Valenciano
            ['abbreviation' => 'gl',    'iso3' => 'ESP'], // Gallego
            ['abbreviation' => 'ext',   'iso3' => 'ESP'], // Extremaduran
            ['abbreviation' => 'ca',    'iso3' => 'ESP'], // Catalán
            ['abbreviation' => 'ocaran','iso3' => 'ESP'], // Aranés
            ['abbreviation' => 'mur',   'iso3' => 'ESP'], // Murciano
            ['abbreviation' => 'an',    'iso3' => 'ESP'], // Aragonés

            // Danés (agregado)
            ['abbreviation' => 'da',    'iso3' => 'DNK'], // Danés => Dinamarca
        ];

        foreach ($languagesIso as $lang) {
            DB::table('languages')
                ->where('abbreviation', $lang['abbreviation'])
                ->update(['iso3_code' => $lang['iso3']]);
        }
    }
}
