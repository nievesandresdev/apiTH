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

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }


    public function build()
    {
       /*  if($this->guest){
            $subject = 'Hola '.$this->guest_name.', prueba la WebApp de '.$this->hotel->name.' ' ;
        }else if($this->create){
            $subject = 'Explora y disfruta la ciudad junto a '. $this->hotel->name;
        }else{
            $subject = 'Te damos la bienvenida a '.$this->hotel->name.'. Descubre todo lo que podemos ofrecerte';
        } */

        $subject = 'Welcome User';


        /* return $this->from("no-reply@thehoster.es", "Hoster Team")
                    ->subject($subject)->view('Mails.users.welcome'); */

                    return $this->from("no-reply@thehoster.es", "Hoster Team")
                    ->subject($subject)->view('Mails.Queries.NewFeedback');


    }

}
