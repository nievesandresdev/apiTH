<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthService
{
    public function __construct(
    ) {}

    public function checkCredentials(Request $request, $guard = 'session-guest'): bool {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        return \Auth::guard($guard)->attempt($credentials);
    }

    public function getModel(Request $request, $guard = 'session-guest'): Guest|User {
        return \Auth::guard($guard)->user();
    }

    public function createToken($model, $guard = 'session-guest'): string {
        return $model->createToken('appToken')->accessToken;
    }

    public function login($model, $guard = 'session-guest'): void {
        \Auth::guard($guard)->login($model);
    }
}