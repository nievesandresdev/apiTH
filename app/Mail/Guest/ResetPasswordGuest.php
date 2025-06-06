<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordGuest extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $hotel;
    public $guest;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($hotel, $url, $guest)
    {
        $this->url = $url;
        $this->hotel = $hotel;
        $this->guest = $guest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $senderName = "Recuperar contraseña";
        /* $senderEmail = "no-reply@thehoster.es";
        if($this->hotel){
            $senderName = $this->hotel['sender_for_sending_email'];

            if($this->hotel['sender_mail_mask']){
                $senderEmail = $this->hotel['sender_mail_mask'];
            }
        } */
        $senderEmail = config('app.mail_sender');
        return $this->from($senderEmail, $senderName)
                    ->subject('Reestrablecer contraseña')->view('Mails.guest.resetPassword');

    }
}
