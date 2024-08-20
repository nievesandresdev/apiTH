<?php
namespace App\Services\Hoster\Stay;

use App\Models\Guest;
use App\Models\Stay;
use App\Models\StayAccess;
use App\Models\StayNotificationSetting;
use App\Services\GuestService;
use App\Utils\Enums\GuestEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaySettingsServices {

    public $guestServices;

    function __construct(GuestService $_GuestService)
    {
        $this->guestServices = $_GuestService;
    }

    public function getAll ($hotelId) {
        try {
            
            $settings =  StayNotificationSetting::where('hotel_id',$hotelId)->first();
            if(!$settings){
                $settings = settingsNotyStayDefault();
            }
            return $settings;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function createMultipleStays(){
        DB::beginTransaction();
        $now = Carbon::now()->format('dm');
        try {
            $guestName = "Huesped";
            
            for ($i=1; $i < 51; $i++) { 
                // Paso 1: Crear o actualizar huésped
                $userName = $guestName.$i.$now;
                $guest = Guest::updateOrCreate(
                    ['email' => $userName."@email.com"],
                    [
                        'name' => $userName,   
                        'lang_web' => 'es',
                        'color'=> GuestEnum::COLORS[0],
                        'acronym' => 'HU',
                    ]
                );
                
                 // Paso 2: Crear la estancia
                $stay = new Stay();
                $stay->hotel_id = 225;  
                $stay->number_guests = 3;
                $stay->language = 'Español';
                $stay->check_in = Carbon::now()->subDays(5)->toDateString();
                $stay->check_out = Carbon::now()->subDay()->toDateString();
                $stay->save();
                
                // Paso 3: Crear registros de acceso
                //relacionar huesped a estancia
                $relation = $guest->stays()->syncWithoutDetaching([$stay->id]);
                return $relation;
                //crear acesso
                $access = new StayAccess();
                $access->stay_id = $stay->id;
                $access->guest_id = $guest->id;
                $access->device = 'Movil';
                $access->save();
            }
            DB::commit();
            return response()->json(['message' => 'Han sido creadas 50 estancias con su respectivo huesped.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("ERROR:createMultipleStays ".$e);
            return $e;
        }
    }

    
}
