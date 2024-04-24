<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Hotel;
use App\Models\User;

use App\Http\Resources\HotelResource;
use App\Models\ChatHour;

class HotelService {

    function __construct()
    {

    }

    public function findByParams ($request) {
        try {
            $subdomain = $request->subdomain ?? null;

            // $query = Hotel::where(function($query) use($subdomain){
            //     if ($subdomain) {
            //         $query->where('subdomain', $subdomain);
            //     }
            // });
            $query = Hotel::whereHas('subdomains', function($query) use($subdomain){
                if ($subdomain) {
                    $query->where('name', $subdomain);
                }
            });

            if (!$subdomain) {
                return null;
            }

            $model = $query->first();

            $data = new HotelResource($model);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById ($id) {
        try {

            $model = Hotel::find($id);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getChatHours ($hotelId) {
        try {
            $defaultChatHours = defaultChatHours();

            $query = ChatHour::where('hotel_id',$hotelId)->where('active',1);

            if (!$query->exists()) {
                return $defaultChatHours;
            }else{
                $chatHours = $query->get();
                return $chatHours;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
}
