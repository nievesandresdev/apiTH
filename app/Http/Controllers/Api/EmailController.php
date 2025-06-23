<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Http;

class EmailController extends Controller
{
    /* public function disabledEmail(Request $request)
    {
        $guest = Guest::find($request->guest_id);
        $guest->off_email = 1;
        $guest->save();
        return bodyResponseRequest(EnumResponse::ACCEPTED, ['status' => true,'guest' => $guest]);
    } */

    public function disabledEmail(Request $request)
    {
        $guest = Guest::findOrFail($request->guest_id);
        $guest->off_email = 1;
        $guest->save();

        try {
            $response = Http::withBasicAuth('api', config('app.mailgun_key'))
                ->asForm()
                ->post("https://api.eu.mailgun.net/v3/" . config('app.mailgun_domain') . "/unsubscribes", [
                    'address' => $guest->email,
                ]);

            if (!$response->successful()) {
                return bodyResponseRequest(EnumResponse::ACCEPTED, [
                    'status' => false,
                    'guest' => $guest,
                    'mailgun_response' => $response->json()
                ]);
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'status' => true,
                'guest' => $guest,
                'mailgun_response' => $response->json()
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'status' => false,
                'guest' => $guest,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function enabledEmail(Request $request)
    {
        $guest = Guest::findOrFail($request->guest_id);
        $guest->off_email = 0;
        $guest->save();

        try {
            // Primero intentamos con DELETE
            $response = Http::withBasicAuth('api', env('MAILGUN_KEY'))
                ->delete("https://api.eu.mailgun.net/v3/" . env('MAILGUN_DOMAIN') . "/unsubscribes/" . $guest->email);

            // Si el DELETE no funciona, intentamos con POST
            if (!$response->successful()) {
                $response = Http::withBasicAuth('api', env('MAILGUN_KEY'))
                    ->asForm()
                    ->post("https://api.eu.mailgun.net/v3/" . env('MAILGUN_DOMAIN') . "/unsubscribes/" . $guest->email . "/remove", []);
            }

            if (!$response->successful()) {
                return bodyResponseRequest(EnumResponse::ACCEPTED, [
                    'status' => false,
                    'guest' => $guest,
                    'mailgun_response' => $response->json()
                ]);
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'status' => true,
                'guest' => $guest,
                'mailgun_response' => $response->json()
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'status' => false,
                'guest' => $guest,
                'error' => $e->getMessage()
            ]);
        }
    }
}
