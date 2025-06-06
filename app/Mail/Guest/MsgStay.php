<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
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
        $this->locale = $guest->lang_web ?? 'es';

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        App::setLocale($this->locale);
        //Log::info('Locale: ',['locale' => $this->locale,'lang_web guest' => $this->guest->lang_web]);

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
        $maskEmail = !empty($this->hotel->sender_mail_mask) ? $this->hotel->sender_mail_mask : config('app.mail_sender');
        $ReceptorEmail = config('app.mail_sender');
        return $this->from($ReceptorEmail, $this->hotel->name)
                    ->sender($maskEmail)
                    ->subject($subject)->view('Mails.guest.msgStay');

    }
}
