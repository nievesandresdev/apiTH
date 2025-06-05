<?php

namespace App\Services;

use App\Services\HttpClientService; 

class DiscordService {

    public $httpClientService;

    public function __construct()
    {
    }

    public function sendMessage($title, $message) {

        $message = "[API] " . $title . "\n" . $message;
        $webhookUrl = config('app.discord_webhook_url');

        $httpClientService = new HttpClientService();
        
        $response = $httpClientService->makeRequest('post',$webhookUrl, [
            'content' => $message,
        ],[
            'Content-Type' => 'application/json',
            'User-Agent' => 'Laravel-App-Webhook',
        ]);
    }

}