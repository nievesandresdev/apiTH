<?php

namespace App\Services;

use App\Models\Guest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class GuestService {

    public function findById($id)
    {
        try {
            return $guest = Guest::find($id);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function saveOrUpdate($data)
    {
        try {
            $email = $data->email;
            $name = $data->name;
            $lang = $data->language;

            $guest = Guest::where('email',$email)->first();
            if(!$guest){
                $guest = Guest::create([
                    'name' =>$name,
                    'email' => $email,
                    'lang_web' => $lang
                ]);
            }else{
                $guest->name = $name;
                $guest->lang_web = $lang;
                $guest->save();
            }
            return $guest;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findLastStay($id,$hotel){
        
        try {
            $guest = Guest::find($id);
            $last_stay = $guest->stays()
                        ->where('hotel_id',$hotel->id)
                        ->orderBy('check_out','DESC')->first();   
            if($last_stay){
                $checkoutDate = $last_stay ? Carbon::parse($last_stay->check_out) : null;
                // Verifica si han pasado más de 10 días desde el checkout

                if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                    //si no han pasado retorna la estancia
                    return $last_stay;
                }
            }
            return null;
        } catch (\Exception $e) {
            return $e;
        }
    }
}