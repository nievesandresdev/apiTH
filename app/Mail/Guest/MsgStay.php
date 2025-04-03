<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
class MsgStay extends Mailable
{
    use Queueable, SerializesModels;
    public $type;
    public $hotel;
    public $guest;
    public $link;
    public $create;
    public $urlQr;
    public $data;
    public $after;
    public $beforeCheckin;
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
        $after = false,
        $beforeCheckin = false
    )
    {
        $this->type = $type;
        $this->hotel = $hotel;
        $this->guest = $guest;
        $this->data = $data;
        $this->after = $after;
        $this->beforeCheckin = $beforeCheckin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Mapa de nombres de idioma a sus acrónimos
        $languageMap = [
            'Español' => 'es',
            'Inglés' => 'en',
            'Italiano' => 'it',
            'Gallego' => 'gl',
            'Catalán' => 'ca',
            'Holandés' => 'nl',
            'Portugués' => 'pt',
            'Francés' => 'fr',
            'Euskera' => 'eu',
        ];

        // Intentar obtener el idioma desde $this->data['stay_language'] (nombre completo)
        if (isset($this->data['stay_language']) && array_key_exists($this->data['stay_language'], $languageMap)) {
            // Si existe un nombre completo válido, obtener el código del idioma
            $locale = $languageMap[$this->data['stay_language']];
        } else {
            // Si no, usar el valor de $this->guest->lang_web que es el código de idioma
            $locale = $this->guest->lang_web;
        }

        // Validar que el valor de $locale esté dentro de los idiomas soportados
        $supportedLocales = ['es', 'en', 'it', 'gl', 'ca', 'nl', 'pt', 'fr', 'eu'];
        $locale = in_array($locale, $supportedLocales) ? $locale : 'es'; // 'es' es el valor por defecto

        //dd($locale);

        // Establecer el idioma
        App::setLocale($this->guest->lang_web ?? 'es');

        // Definir el asunto traducido según el tipo
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

        // if($this->type == 'welcome'){
        //     $subject = 'Hola '.$this->guest_name.', prueba la WebApp de '.$this->hotel->name.' ' ;
        // }
        // else if($this->create){
        //     $subject = 'Explora y disfruta la ciudad junto a '. $this->hotel->name;
        // }else{
        // $subject = 'Te damos la bienvenida a '.$this->hotel->name.'. Descubre todo lo que podemos ofrecerte';
        // }

        $senderName = $this->hotel->sender_for_sending_email;
        $senderEmail = $this->hotel->sender_mail_mask ??  "no-reply@thehoster.es";
        if($this->hotel->sender_mail_mask){
            $senderEmail = $this->hotel->sender_mail_mask;
        }
        return $this->from($senderEmail, $this->hotel->name)
                    ->subject($subject)->view('Mails.guest.msgStay');

    }
}
