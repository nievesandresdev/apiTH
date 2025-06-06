<?php

namespace App\Mail\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUser extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $url;
    public $password;
    public $userAuth;

    /**
     * Create a new message instance.
     */
    public function __construct($user,$url,$password,$userAuth)
    {
        $this->user = $user;
        $this->url = $url;
        $this->password = $password;
        $this->userAuth = $userAuth;
    }


    public function build()
    {

        $subject = 'Bienvendio a TheHoster';
        $senderEmail = config('app.mail_sender');


        return $this->from($senderEmail, "Hoster Team")
                    ->subject($subject)->view('Mails.users.welcome');


    }

}
