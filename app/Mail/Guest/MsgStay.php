<?php

namespace App\Mail\Guest;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Symfony\Component\Mime\Email;  // <--- Import correcto

class MsgStay extends Mailable
{
    use SerializesModels;

    public $type;
    public $hotel;
    public $guest;
    public $data;
    public $after;
    public $beforeCheckin;
    public $locale;

    public function __construct(
        string $type,
        $hotel,
        $guest,
        $data = null,
        bool $after = false,
        bool $beforeCheckin = false
    ) {
        $this->type          = $type;
        $this->hotel         = $hotel;
        $this->guest         = $guest;
        $this->data          = $data;
        $this->after         = $after;
        $this->beforeCheckin = $beforeCheckin;
        $this->locale        = $guest->lang_web ?? 'es';
    }

    public function envelope(): Envelope
    {
        App::setLocale($this->locale);

        switch ($this->type) {
            case 'welcome':
            case 'inviteGuestFromSaas':
                $subject = __('mail.welcome.subject', ['hotel' => $this->hotel->name]);
                break;
            case 'postCheckin':
                $subject = __('mail.postCheckin.subject');
                break;
            default:
                $subject = __('mail.default.subject');
                break;
        }

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
            view: 'Mails.guest.msgStay'
        );
    }
}
