<?php

namespace App\Services;

use App\Services\HttpClientService; 

class NotificationDiscordService {

    public $httpClientService;

    public function __construct()
    {
    }

    public function sendMessage($title, $message) {
        $httpClientService = new HttpClientService();
        $webhookUrl = config('app.discord_webhook_url');
        $message = "[API] " . $title . "\n" . $message;
        $response = $httpClientService->makeRequest('post',$webhookUrl, [
            'content' => $message,
        ],[
            'Content-Type' => 'application/json',
            'User-Agent' => 'Laravel-App-Webhook',
        ]);
    }

}