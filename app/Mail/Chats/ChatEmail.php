<?php

namespace App\Mail\Chats;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $unansweredMessagesData;
    public $type;
    //public $hotel;

    /**
     * Create a new message instance.
     */
    public function __construct($unansweredMessagesData,$type)
    {
        $this->unansweredMessagesData = $unansweredMessagesData;
        $this->type = $type;
        //$this->hotel = $hotel;
    }

    public function build()
    {
        $typeTitle = "Nuevo Chat";
        if ($this->type == 'pending') {
            $typeTitle = "Chat pendiente";
        }

        /* return $this->from("no-reply@thehoster.es", $this->hotel['sender_for_sending_email'])
                    ->subject($typeTitle)
                    ->view('Mails.queries.NewFeedback')
                    ->with([
                        'languageName' => $this->languageName,
                    ]); */

                    return $this->from("no-reply@thehoster.es", 'ssss@gmail.com')
                    ->subject($typeTitle)
                    ->view('Mails.chats.NewChat');
    }
}
