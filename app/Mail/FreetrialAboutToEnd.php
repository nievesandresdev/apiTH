<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreetrialAboutToEnd extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $appName;

    public function __construct($user)
    {
        $this->user = $user;
        $this->appName = config('app.name');
    }

    public function build()
    {
        return $this->subject($this->user['name'].", tu periodo de prueba con The Hoster está por terminar")->view('Mails.freetrialAboutToEnd');
    }
}
