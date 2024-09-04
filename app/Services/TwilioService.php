<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendWhatsAppMessage($from, $to, $message)
    {
        $this->client->messages->create(
            "whatsapp:$to", // Número de destino
            [
                'from' => "whatsapp:$from", // Número de origen (remitente)
                'body' => $message
            ]
        );
    }
}
