<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientContactMessage extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $email;
    public $phone;
    public $appName;
    public $msg;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $phone, $msg)
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->msg = $msg;
        $this->appName = config('app.name');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('app.name') . " - Mensaje de contacto por ". $this->name)->view('Mails.clientcontact');
    }
}