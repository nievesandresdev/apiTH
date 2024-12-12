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
    public $create;
    public $urlQr;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg, $hotel,$link = null,$guest = false,$guest_name = null,$create = false,$urlQr = null)
    {
        $this->msg = $msg;
        $this->hotel = $hotel;
        $this->link = $link;
        $this->guest = $guest;
        $this->guest_name = $guest_name;
        $this->create = $create;
        $this->urlQr = $urlQr;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->guest){
            $subject = 'Hola '.$this->guest_name.', prueba la WebApp de '.$this->hotel->name.' ' ;
        }else if($this->create){
            $subject = 'Explora y disfruta la ciudad junto a '. $this->hotel->name;
        }else{
            $subject = 'Te damos la bienvenida a '.$this->hotel->name.'. Descubre todo lo que podemos ofrecerte';
        }

        $senderName = $this->hotel['sender_for_sending_email'];
        $senderEmail = "no-reply@thehoster.es";
        if($this->hotel['sender_mail_mask']){
            $senderEmail = $this->hotel['sender_mail_mask'];
        }
        return $this->from($senderEmail, $senderName)
                    ->subject($subject)->view('Mails.guest.msgStay');

    }
}
