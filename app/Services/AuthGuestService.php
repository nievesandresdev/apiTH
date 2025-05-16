<?php

namespace App\Services;

use App\Services\AuthService;

class AuthGuestService extends AuthService
{
    public function __construct(
        GuestService $_GuestService,
    )
    {
        $this->guestService = $_GuestService;
    }

    public function autenticateByGoogle(Request $request){
        $guestModel = $this->guestService->findByGoogleId($request->googleId);
        if(!$guestModel){
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED, ['message' => 'No se encontró el usuario']);
        }
        $this->login($guestModel);
        
        $token = $this->createToken($guestModel);
        return $token;
    }
}