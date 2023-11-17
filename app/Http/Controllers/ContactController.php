<?php

namespace App\Http\Controllers;

use App\Mail\ClientContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send_message_to_thehoster(Request $request)
    {
        $data = $request->data;
        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $msg = $data['more'];
        
        Mail::to("contacto@thehoster.es")->send(new ClientContactMessage($name, $email, $phone, $msg));
        return response()->json([
            'message' => 'Mensaje enviado con éxito',
            'type' => 'success',
        ]);
    }

}
