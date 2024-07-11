<?php

namespace App\Services\Hoster\Chat;

class ChatHosterServices {


    public function pendingCountByHotel($hotel){
        try {
            return $hotel->stays()
            ->whereHas('chats', function ($query) {
                $query->where('pending', 1);
            })
            ->count();
        } catch (\Exception $e) {
            return $e;
        }
    }
    
}
