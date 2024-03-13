<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewFeedback extends Mailable
{
    use Queueable, SerializesModels;
    public $dates;
    public $url;
    public $hotel;
    public $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dates, $url, $hotel , $type)
    {
        $this->dates = $dates;
        $this->url = $url;
        $this->hotel = $hotel;
        $this->type = $type;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $typeTitle = "Nuevo feedback";
        if($this->type == 'pending'){
            $typeTitle = "Feedback pendiente";
        }
        return $this->from("info@".$this->hotel->subdomain.".com", $this->hotel->name)
                    ->subject($typeTitle)->view('Mails.queries.NewFeedback');
        
    }
}
