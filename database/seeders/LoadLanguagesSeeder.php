<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Language;

class LoadLanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newLanguages = [
            ['abbreviation' => 'ca', 'name' => 'Catalán', 'active' => 1],
            ['abbreviation' => 'eu', 'name' => 'Euskera', 'active' => 1],
            ['abbreviation' => 'gl', 'name' => 'Gallego', 'active' => 1],
            ['abbreviation' => 'nl', 'name' => 'Neerlandés', 'active' => 1]
        ];

        foreach ($newLanguages as $key => $newLanguage) {
            Language::UpdateOrCreate([
                'abbreviation' => $newLanguage['abbreviation']
            ], [
                'name' => $newLanguage['name'],
                'abbreviation' => $newLanguage['abbreviation'],
                'active' => $newLanguage['active']
            ]);
        }
    }
}
