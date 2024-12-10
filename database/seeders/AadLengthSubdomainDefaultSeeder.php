<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ChainSubdomain;

class AadLengthSubdomainDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = ChainSubdomain::all();
        foreach ($models as $model) {
            $lengthSubdomainDefault = 
            $model->length_subdomain_default = strlen($model->name);
            $model->save();
        }
    }
}
