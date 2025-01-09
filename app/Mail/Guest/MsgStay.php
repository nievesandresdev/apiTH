<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MsgStay extends Mailable
{
    use Queueable, SerializesModels;
    public $type;
    public $hotel;
    public $guest;
    public $link;
    public $create;
    public $urlQr;
    public $data;
    public $after;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $type,
        $hotel,
        $guest,
        $data = null,
        $after = false
    )
    {
        $this->type = $type;
        $this->hotel = $hotel;
        $this->guest = $guest;
        $this->data = $data;
        $this->after = $after;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->type == 'welcome' || $this->type == 'inviteGuestFromSaas'){
            $subject = '¿Ya conoces la WebApp de '.$this->hotel->name.'?';
        }else{
            $subject = 'Gracias por elegirnos.';
        }

        // if($this->type == 'welcome'){
        //     $subject = 'Hola '.$this->guest_name.', prueba la WebApp de '.$this->hotel->name.' ' ;
        // }
        // else if($this->create){
        //     $subject = 'Explora y disfruta la ciudad junto a '. $this->hotel->name;
        // }else{
        // $subject = 'Te damos la bienvenida a '.$this->hotel->name.'. Descubre todo lo que podemos ofrecerte';
        // }

        $senderName = $this->hotel['sender_for_sending_email'];
        $senderEmail = "no-reply@thehoster.es";
        if($this->hotel['sender_mail_mask']){
            $senderEmail = $this->hotel['sender_mail_mask'];
        }
        return $this->from($senderEmail, $senderName)
                    ->subject($subject)->view('Mails.guest.msgStay');

    }
}
