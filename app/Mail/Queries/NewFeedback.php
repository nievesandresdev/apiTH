<?php

namespace App\Mail\Queries;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewFeedback extends Mailable
{
    use Queueable, SerializesModels;
    public $dates;
    public $url;
    public $hotel;
    public $query;
    public $guest;
    public $stay;
    public $type;
    public $languageName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dates, $url, $hotel, $query,$guest,$stay, $type)
    {
        $this->dates = $dates;
        $this->url = $url;
        $this->hotel = $hotel;
        $this->query = $query;
        $this->guest = $guest;
        $this->stay = $stay;
        $this->type = $type;

        // Asigna el nombre del idioma basado en response_lang usando EnumsLanguages
        $this->languageName = lenguageName($this->query['response_lang']) ?? $this->query['response_lang'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $typeTitle = "Nuevo feedback";
        if ($this->type == 'pending') {
            $typeTitle = "Feedback pendiente";
        }

        return $this->from("no-reply@thehoster.es", $this->hotel['sender_for_sending_email'])
                    ->subject($typeTitle)
                    ->view('Mails.queries.NewFeedback')
                    ->with([
                        'languageName' => $this->languageName,
                    ]);
    }
}

