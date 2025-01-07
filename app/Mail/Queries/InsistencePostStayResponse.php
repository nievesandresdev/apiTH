<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InsistencePostStayResponse extends Mailable
{
    use Queueable, SerializesModels;
    
    public $hotel;
    public $access_url;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($access_url ,$hotel)
    {
        $this->hotel = $hotel;
        $this->access_url = $access_url;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from("no-reply@thehoster.es", $this->hotel->sender_for_sending_email)
                    ->subject('Responde la consulta post-stay')->view('Mails.queries.InsistencePostStayResponse');
        
    }
}
