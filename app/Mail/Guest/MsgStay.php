<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class MsgStay extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $type;
    public $hotel;
    public $guest;
    public $data;
    public $after;
    public $beforeCheckin;
    public $locale;

    /**
     * Create a new message instance.
     *
     * @param  string       $type
     * @param  \App\Models\Hotel  $hotel
     * @param  \App\Models\Guest  $guest
     * @param  mixed|null   $data
     * @param  bool         $after
     * @param  bool         $beforeCheckin
     * @return void
     */
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

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        // 1) Asignar el locale antes de generar asunto/traducciones
        App::setLocale($this->locale);

        // 2) Calcular el asunto según el tipo
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

        // 3) Dirección que maneja SMTP (MAIL FROM técnico)
        $smtpSender = config('app.mail_sender'); 
        // 4) “Máscara” que ve el usuario final (Sender/Header)
        $maskEmail = !empty($this->hotel->sender_mail_mask)
                     ? $this->hotel->sender_mail_mask
                     : config('app.mail_sender');

        // 5) Nombre que se muestra como “From” (puede ser el nombre del hotel)
        $fromName = $this->hotel->name;

        return new Envelope(
            from:   new Address($smtpSender, $fromName),
            sender: new Address($maskEmail),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        // Aseguramos que el locale ya esté establecido (por si alguien llama a content sin pasar por envelope)
        App::setLocale($this->locale);

        return new Content(
            view: 'Mails.guest.msgStay'
        );
    }
}
