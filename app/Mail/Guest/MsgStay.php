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
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg, $hotel)
    {
        $this->msg = $msg;
        $this->hotel = $hotel;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from("info@".$this->hotel->subdomain.".com", $this->hotel->name)
                    ->subject($this->hotel->name)->view('Mails.guest.msgStay');
        
    }
}
