<?php

namespace App\Services;

use App\Models\Guest;
use Illuminate\Http\Request;

class AuthService
{
    public function __construct(
    ) {}

    public function checkCredentials(Request $request, $guard = 'session-guest') {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        return \Auth::guard($guard)->attempt($credentials);
    }

    public function getModel(Request $request, $guard = 'session-guest') {
        return \Auth::guard($guard)->user();
    }

    public function createToken($model) {
        return $model->createToken('appToken')->accessToken;
    }
}