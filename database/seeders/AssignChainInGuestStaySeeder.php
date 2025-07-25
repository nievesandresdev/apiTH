<?php

namespace Database\Seeders;

use App\Models\GuestStay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssignChainInGuestStaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = GuestStay::all();
        foreach ($models as $model) {
            if($model && $model->stay){
                Log::info('model '.json_encode($model->stay->hotel));
                $model->chain_id = $model->stay->hotel->chain_id;
                $model->save();
            }
        }
    }
}
