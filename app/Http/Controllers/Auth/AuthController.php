<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('web')->attempt($credentials))
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticaded',
            ], 401);

            $user = Auth::guard('web')->user();

            $token = $user->createToken('appToken')->accessToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
            ], 200);
    }

    public function getUsers(Request $request)
    {
        return response()->json($request->user()->load('profile'));
    }
}
