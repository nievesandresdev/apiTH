<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestReviewGuest extends Mailable
{
    use Queueable, SerializesModels;
    public $hotel;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($hotel)
    {
        $this->hotel = $hotel;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from("no-reply@thehoster.es", $this->hotel['sender_for_sending_email'])
                    ->subject('solicitud de reseña')->view('Mails.queries.RequestReviewGuest');
        
    }
}
