<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject(Lang::get('Restablecimiento de contraseña'))
                    ->line(Lang::get('Está recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta.'))
                    ->action(Lang::get('Restablecer contraseña'), config('app.hoster_url')."reset-password/{$this->token}?email={$notifiable->getEmailForPasswordReset()}")
                    ->line(Lang::get('Este enlace de restablecimiento de contraseña expirará en :count minutos.', ['count' => config('auth.passwords.users.expire')]))
                    ->line(Lang::get('Si no solicitó un restablecimiento de contraseña, no se requiere ninguna acción adicional.'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

