<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use App\Utils\Enums\EnumResponse;

class EmailController extends Controller
{
    public function disabledEmail(Request $request)
    {
        $guest = Guest::find($request->guest_id);
        $guest->off_email = 1;
        $guest->save();
        return bodyResponseRequest(EnumResponse::ACCEPTED, ['status' => true,'guest' => $guest]);
    }

    public function enabledEmail(Request $request)
    {
        $guest = Guest::find($request->guest_id);
        $guest->off_email = 0;
        $guest->save();
        return bodyResponseRequest(EnumResponse::ACCEPTED, ['status' => true,'guest' => $guest]);
    }
}
