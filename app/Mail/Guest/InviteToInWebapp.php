<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteToInWebapp extends Mailable
{
    use Queueable, SerializesModels;

    public $hotel;
    public $crosselling;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($hotel,$crosselling)
    {
        $this->hotel = $hotel;
        $this->crosselling = $crosselling;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $senderName = $this->hotel['sender_for_sending_email'];
        $senderEmail = "no-reply@thehster.io";
        if($this->hotel['sender_mail_mask']){
            $senderEmail = $this->hotel['sender_mail_mask'];
        }
        return $this->from($senderEmail, $this->hotel->name)
                    ->subject('Asunto test')->view('Mails.guest.InviteToInWebapp');

    }
}
