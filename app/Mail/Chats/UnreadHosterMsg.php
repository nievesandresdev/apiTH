<?php

namespace App\Mail\Chats;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UnreadHosterMsg extends Mailable
{
    use Queueable, SerializesModels;
    public $unansweredMessagesData;
    public $hotel;
    public $webappLink;
    public $qrImage;
    //public $hotel;

    /**
     * Email para avisar al huesped que tiene mensajes sin leer
     */
    public function __construct($unansweredMessagesData,$hotel,$webappLink)
    {
        $this->unansweredMessagesData = $unansweredMessagesData ?? [];
        $this->hotel = $hotel;
        $this->webappLink = $webappLink;
        $qrImage = null;
        try {
            $qrImage = base64_encode(
                QrCode::format('png')->size(200)->generate($this->webappLink)
            );
        } catch (\Exception $e) {
            // Manejar la excepción o registrar el error
            Log::error('Error generando el código QR: ' . $e->getMessage());
            $qrImage = null; // O algún valor predeterminado
        }
        $this->qrImage = $qrImage;
        // $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($this->webappLink));
        // $this->qrImage = $qrImage;
        
    }

    public function build()
    {

        $senderName = $this->hotel['sender_for_sending_email'];
        $senderEmail = "no-reply@thehoster.es";
        if($this->hotel['sender_mail_mask']){
            $senderEmail = $this->hotel['sender_mail_mask'];
        }
        Log::info('qrimage '.json_decode($this->qrImage));
        return $this->from($senderEmail, $senderName)
                    ->subject("Mensaje pendiente en Chat")->view('Mails.guest.unreadMsg');
    }
}
