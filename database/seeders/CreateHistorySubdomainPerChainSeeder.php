<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Chain;
use App\Models\ChainSubdomain;


class CreateHistorySubdomainPerChainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chainCollection = Chain::all();
        foreach ($chainCollection as $model) {
            ChainSubdomain::updateOrCreate([
                'chain_id' => $model->id
            ],[
                'name' => $model->subdomain,
                'chain_id' => $model->id,
                'active' => 1
            ]);
        }
    }
}
