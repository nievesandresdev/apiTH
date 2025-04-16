<?php

namespace App\Mail\Chats;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ChatEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $unansweredMessagesData;
    public $type;
    public $url;
    public $time;
    public $user;
    public $idUser;
    public $hotel;
    public $data;
    /**
     * Create a new message instance.
     */
    public function __construct($unansweredMessagesData,$url,$time = null,$idUser = null,$type='new',$hotel = null,$data = [])
    {
        $this->unansweredMessagesData = $unansweredMessagesData ?? [];
        $this->type = $type;
        $this->url = $url;
        $this->time = $time;
        $this->idUser = $idUser;
        $this->hotel = $hotel;
        $this->data = $data;
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

                    return $this->from("no-reply@thehoster.es", 'Nuevo Chat')
                    ->subject($typeTitle)
                    ->view('Mails.chats.NewChat');
    }
}
