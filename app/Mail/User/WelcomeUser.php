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

    /**
     * Create a new message instance.
     */
    public function __construct($user,$url,$password)
    {
        $this->user = $user;
        $this->url = $url;
        $this->password = $password;
    }


    public function build()
    {

        $subject = 'Bienvendio a TheHoster';


        return $this->from("no-reply@thehoster.es", "Hoster Team")
                    ->subject($subject)->view('Mails.users.welcome');


    }

}
