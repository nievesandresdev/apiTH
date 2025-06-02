<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class ReportHoster extends Mailable
{
    use Queueable;

    public $hotel;
    public $showNotify;
    public $stats;
    public $links;

    public function __construct($hotel, $showNotify, $stats, $links)
    {
        $this->hotel = $hotel;
        $this->showNotify = $showNotify;
        $this->stats = $stats;
        $this->links = $links;
    }

    public function build()
    {
        return $this->subject('Informe de Seguimiento')
            ->view('Mails.queries.ReportHoster')
            ->with([
                'hotel' => $this->hotel,
                'showNotify' => $this->showNotify,
                'stats' => $this->stats,
                'links' => $this->links,
            ]);

    }
}
