<?php

namespace App\Http\Controllers;

use App\Mail\ClientContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Services\MailService;

class ContactController extends Controller
{

    public function __construct(MailService $_MailService)
    {
        $this->mailService = $_MailService;
    }

    public function send_message_to_thehoster(Request $request)
    {
        $data = $request->data;
        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $msg = $data['more'];
        
        // Maiil::to("contacto@thehoster.es")->send(new ClientContactMessage($name, $email, $phone, $msg));
        $this->mailService->sendEmail(new ClientContactMessage($name, $email, $phone, $msg), "contacto@thehoster.es");
        return response()->json([
            'message' => 'Mensaje enviado con éxito',
            'type' => 'success',
        ]);
    }

}
