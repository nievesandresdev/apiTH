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
            $lengthSubdomainDefault = strlen($model->name);
            $model->length_subdomain_default = $lengthSubdomainDefault > 20 ? $lengthSubdomainDefault : 20;
            $model->save();
        }
    }
}
