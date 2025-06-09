<?php

namespace App\Mail\Guest;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Symfony\Component\Mime\Email;
class prepareArrival extends Mailable
{
    use SerializesModels;
    public $type;
    public $hotel;
    public $guest;
    public $link;
    public $create;
    public $urlQr;
    public $data;
    public $after;
    public $locale;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $type,
        $hotel,
        $guest,
        $data = null,
        $after = false
    )
    {
        $this->type = $type;
        $this->hotel = $hotel;
        $this->guest = $guest;
        $this->data = $data;
        $this->after = $after;
        $this->locale = $guest->lang_web ?? 'es';
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->locale);

        $subject = __('mail.prepareArrival.subject', ['guest_name' => $this->guest->name]);

        $smtpSender = config('app.mail_sender');
        $maskEmail  = !empty($this->hotel->sender_mail_mask)
                    ? $this->hotel->sender_mail_mask
                    : config('app.mail_sender');
        $fromName   = $this->hotel->name;

        return new Envelope(
            from: new Address($maskEmail, $fromName),
            subject: $subject,
            using: [
                function (Email $message) use ($smtpSender) {
                    $message->sender($smtpSender);
                },
            ],
        );
    }

    public function content(): Content
    {
        App::setLocale($this->locale);

        return new Content(
            view: 'Mails.guest.prepareYourArrival'
        );
    }
}
