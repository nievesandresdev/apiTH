<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Envía un correo electrónico a un usuario, a menos que el correo contenga "@thehoster.es".
     *
     * @param  \App\Mail\Mailable $mailable
     * @param  string $email
     * @return void
     * @throws \Exception
     */
    public function sendEmail($mailable, $email)
    {
        if (!$email) return;
        $emails_available = ['general@thehoster.es', 'contacto@thehoster.es', 'info@thehoster.es'];
        if (preg_match('/@thehoster\.es$/', $email) && !in_array($email, $emails_available)) {
            return;
        }
    

        Mail::to($email)->send($mailable);
    }
}