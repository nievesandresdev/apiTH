<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class HttpClientService
{
    public function __construct () {
        $this->client = new Client();
    }

    public function make_request($method, $url, $data = [], $headers = [], $timeout = 960)
    {
        try {
            $response = Http::timeout($timeout)->withHeaders($headers)->{$method}($url, $data);
            if ($response->successful()) {
                return $response->json();
            }

            // Manejo de errores específicos aquí
            if ($response->clientError()) {
                \Log::error("$url - Error de cliente HTTP");
                return [
                    "ok" => false,
                    "error" => true,
                    "message" => " Error de cliente HTTP",
                    "body"=> $response->body() ?? null
                ];
            } elseif ($response->serverError()) {
                \Log::error("$url - Error de servidor HTTP");
                return [
                    "ok" => false,
                    "error" => true,
                    "message" => " Error de servidor HTTP",
                    "body"=> $response->body() ?? null
                ];
            }

            // Manejo de otros tipos de errores
            // ...

            return null; // o manejar según sea necesario
        } catch (\Exception $e) {
            \Log::error("$url - Error de HTTP: " . $e->getMessage());
            $message = $e->getMessage();
            return [
                "error" => true,
                "message" => $message
            ];
        }
    }
}
