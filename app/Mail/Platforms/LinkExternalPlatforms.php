<?php

namespace App\Mail\Platforms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LinkExternalPlatforms extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $hotel;

    /**
     * Create a new message instance.
     */
    public function __construct($data,$hotel)
    {
        $this->data = $data;
        $this->hotel = $hotel;
    }


    public function build()
    {
        $typeTitle = "Cambio de enlace en plataforma externa [{$this->hotel->name}]";


        return $this->from("no-reply@thehoster.es", 'info@thehoster.es')
            ->subject($typeTitle)
            ->view('Mails.platforms.ChangeUrl');
    }
}
