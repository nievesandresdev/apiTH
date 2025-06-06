<?php

namespace App\Mail\Test;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class TestMailer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Define los encabezados principales (From, Sender, Subject).
     */
    public function envelope(): Envelope
    {
        // 1) Dirección visible en "From:" (el destinatario verá esta máscara)
        $maskEmail  = 'info@taykosevilla.com';
        $fromName   = 'Mi Hotel de Prueba';

        // 2) Dirección técnica que usará SMTP (Return-Path)
        $smtpSender = config('app.mail_sender');

        return new Envelope(
            from: new Address($maskEmail, $fromName),
            subject: 'Test Mailer',
            using: [
                function (Email $message) use ($smtpSender) {
                    $message->sender($smtpSender);
                },
            ],
        );
    }

    /**
     * Define el contenido (vista) que se renderizará.
     */
    public function content(): Content
    {
        return new Content(
            view: 'Mails.Test.test'
        );
    }
}
