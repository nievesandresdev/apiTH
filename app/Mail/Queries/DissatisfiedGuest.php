<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
class DissatisfiedGuest extends Mailable
{
    use Queueable;

    public $hotel;
    public $showNotify;
    public $data;

    public function __construct($hotel, $showNotify, $data)
    {
        $this->hotel = $hotel;
        $this->showNotify = $showNotify;
        $this->data = $data;
    }

    public function build()
    {
        $subject = 'Huésped disconforme: '.$this->data['guestName'].' - '.$this->data['respondedAtFormatted'];

        $senderEmail = config('app.mail_sender');

        return $this->from($senderEmail, $this->hotel->name)
            ->subject($subject)
            ->view('Mails.queries.DissatisfiedGuest')
            ->with([
                'hotel' => $this->hotel,
                'showNotify' => $this->showNotify,
                'data' => $this->data,
            ]);

    }
}

