<?php

namespace App\Mail\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RewardsEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $hotel;
    public $data;
    public $rewardStay;

    public function __construct(
        $hotel,
        $rewardStay,
        $data = null,
    )
    {
        $this->hotel = $hotel;
        $this->rewardStay = $rewardStay;
        $this->data = $data;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = '¡Enhorabuena! Has recibido un regalo';

        $senderName = $this->hotel->sender_for_sending_email;
        $senderEmail = $this->hotel->sender_mail_mask ??  "no-reply@thehoster.es";
        if($this->hotel->sender_mail_mask){
            $senderEmail = $this->hotel->sender_mail_mask;
        }
        return $this->from($senderEmail, $senderName)
                    ->subject($subject)->view('Mails.users.rewards');

    }
}
