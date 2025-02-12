<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class postCheckoutMail extends Mailable
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
        $subject = 'Gracias por elegirnos.';

        $senderName = $this->hotel->sender_for_sending_email;
        $senderEmail = $this->hotel->sender_mail_mask ??  "no-reply@thehoster.es";
        if($this->hotel->sender_mail_mask){
            $senderEmail = $this->hotel->sender_mail_mask;
        }
        return $this->from($senderEmail, $this->hotel->name)
                    ->subject($subject)->view('Mails.guest.postCheckoutEmail');

    }
}
