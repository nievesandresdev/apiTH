<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class DissatisfiedGuest extends Mailable
{
    use Queueable;

    public $hotel;
    public $showNotify;

    public function build()
    {
        return $this->view('mails.queries.DissatisfiedGuest')
            ->with([
                'hotel' => $this->hotel,
                'showNotify' => $this->showNotify,
            ]);
        
    }
}

