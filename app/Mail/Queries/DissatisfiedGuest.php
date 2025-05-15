<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

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
        return $this->view('mails.queries.DissatisfiedGuest')
            ->with([
                'hotel' => $this->hotel,
                'showNotify' => $this->showNotify,
                'data' => $this->data,
            ]);
        
    }
}

