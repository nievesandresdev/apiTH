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

        $lang = $this->guest->lang_web ?? 'es'; //default'es'
        App::setLocale($lang); // cambiar idioma

        $subject = __('mail.subject_default');
        if($this->type == 'welcome' || $this->type == 'inviteGuestFromSaas'){
            $subject = __('mail.subject_welcome', ['hotel' => $this->hotel->name]);
        }
        if($this->type == 'postCheckin'){
            $subject = __('mail.subject_post_checkin');
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
