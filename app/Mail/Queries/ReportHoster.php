<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class ReportHoster extends Mailable
{
    use Queueable;

    public $hotel;
    public $showNotify;

    public function build()
    {
        return $this->view('mails.queries.ReportHoster')
            ->with([
                'hotel' => $this->hotel,
                'showNotify' => $this->showNotify,
            ]);
        
    }
}
