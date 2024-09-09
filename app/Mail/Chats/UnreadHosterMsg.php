<?php

namespace App\Mail\Chats;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadHosterMsg extends Mailable
{
    use Queueable, SerializesModels;
    public $unansweredMessagesData;
    public $hotel;
    public $webappLink;
    //public $hotel;

    /**
     * Email para avisar al huesped que tiene mensajes sin leer
     */
    public function __construct($unansweredMessagesData,$hotel,$webappLink)
    {
        $this->unansweredMessagesData = $unansweredMessagesData ?? [];
        $this->hotel = $hotel;
        $this->webappLink = $webappLink;
    }

    public function build()
    {

        $senderName = $this->hotel['sender_for_sending_email'];
        $senderEmail = "no-reply@thehoster.es";
        if($this->hotel['sender_mail_mask']){
            $senderEmail = $this->hotel['sender_mail_mask'];
        }
        return $this->from($senderEmail, $senderName)
                    ->subject("Mensaje sin leer")->view('Mails.guest.unreadMsg');
    }
}
