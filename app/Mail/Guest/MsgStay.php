<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MsgStay extends Mailable
{
    use Queueable, SerializesModels;
    public $msg;
    public $hotel;
    public $guest;
    public $guest_name;
    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg, $hotel,$guest = false,$guest_name = null, $link = null)
    {
        $this->msg = $msg;
        $this->hotel = $hotel;
        $this->guest = $guest;
        $this->guest_name = $guest_name;
        $this->link = $link;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->guest =! null){
            $subject = 'Hola '.$this->guest_name.', prueba la WebApp de '.$this->hotel->name.' ' ;
        }else{
            $subject = 'Te damos la bienvenida a '.$this->hotel->name.'. Descubre todo lo que podemos ofrecerte';
        }

        return $this->from("no-reply@thehoster.es", $this->hotel['sender_for_sending_email'])
                    ->subject($subject)->view('Mails.guest.msgStay');

    }
}
