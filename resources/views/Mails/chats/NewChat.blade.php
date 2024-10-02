<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Chat</title>
    <style>
        @media only screen and (max-width: 600px) {
            body {
                background-color: #ffffff !important;
            }
            .responsive-table, .responsive-table-2 {
                width: 100% !important;
                display: block !important;
            }
            .responsive-table td, .responsive-table-2 td {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                padding: 10px 0 !important;
            }
            .responsive-table .text-content, .responsive-table-2 .text-content {
                text-align: left !important;
            }
            .full-width-button {
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }
            .image-frame {
                height: auto !important;
            }
            .responsive-table .order-1 {
                order: 1;
            }
            .responsive-table .order-2 {
                order: 2;
            }
            .responsive-table .order-3 {
                order: 3;
                display: none !important;
            }
            .responsive-table .order-4 {
                order: 3;
                display: block !important;
            }
        }

        .order-4 {
            display: none !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA; font-family: Arial, sans-serif;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffff;">
        <div style="background-color: white; text-align: center; padding-top: 16px; padding-bottom:24px;">
            <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding-top: 40px; padding-bottom:40px; padding-right:32px; padding-left:32px;">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family: Arial, sans-serif;">
                <tr>
                    <td class="text-content order-1" style="color: white; padding-right: 40px; text-align: left; width: 50%; vertical-align: top; font-family: Arial, sans-serif;">
                        @if($type == 'pending')
                            <span style="font-size: 32px; font-style: normal; font-weight: 600; line-height: 110%; margin: 0; font-family: Arial, sans-serif;">¡Tienes un chat pendiente!</span>
                            <span style="display: block; margin: 10px 0 22px 0; font-size: 16px; font-style: normal; font-weight: 500; line-height: 130%; font-family: Arial, sans-serif;">Han transcurrido <span style="color: #FFD453;">{{ $time }} minutos</span> desde que un huésped te ha escrito un mensaje desde el chat</span>
                        @else
                            <span style="font-size: 32px; font-style: normal; font-weight: 600; line-height: 110%; margin: 0; font-family: Arial, sans-serif;">Tienes un nuevo mensaje en el chat</span>
                            <span style="display: block; margin: 10px 0 22px 0; font-size: 16px; font-style: normal; font-weight: 500; line-height: 130%; font-family: Arial, sans-serif;">Has recibido un nuevo mensaje de un huésped. Puedes ir al mensaje dando click al botón de abajo.</span>
                        @endif
                        <!-- Botón que se muestra en el modo no responsive -->
                        <div style="text-align: center; margin-top: 22px;">
                            <a href="{{$url}}" class="full-width-button order-3" style="display: inline-block; padding: 12px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 600; width: 100%; height: 45px; box-sizing: border-box; font-family: Arial, sans-serif;">Atender Chat</a>
                        </div>
                    </td>
                    <td class="order-2" style="text-align: center; width: 50%; vertical-align: top;">
                        @if($type == 'pending')
                            <img src="{{ asset('mails/chat-pending.png') }}" alt="Chat Image" style="width: 227px; height: 240px;">
                        @else
                            <img src="{{ asset('mails/chat.png') }}" alt="Chat Image" style="width: 227px; height: 240px;">
                        @endif
                        <!-- Botón que se muestra en el modo responsive -->
                        <div style="text-align: center; margin-top: 22px;">
                            <a href="{{$url}}" class="full-width-button order-4" style="display: inline-block; padding: 12px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 600; width: 100%; height: 45px; box-sizing: border-box; font-family: Arial, sans-serif;">Atender Chat</a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Nueva sección añadida aquí -->
        <div style="background-color: white; padding: 20px; text-align: left;">
            <h2 style="margin: 0; font-weight: 600; font-family: Arial, sans-serif;">Mensaje</h2>
            @if($type == 'pending')
                @foreach($unansweredMessagesData as $chat)
                    <section style="margin-bottom: 20px;">
                        <p style="margin-top: 20px; font-weight: 600; font-size: 16px; font-family: Arial, sans-serif;">{{ $chat['guest_name'] }}</p>
                        <div style="border-radius: 6px 6px 0px 6px; padding: 12px; margin: 10px 0; background-color: #ffffff; border: 1px solid #E0E0E0; font-size: 16px; font-weight: 400; line-height: 150%; box-shadow: 0px 2px 11.2px 0px rgba(55, 55, 55, 0.20); font-family: Arial, sans-serif;">
                            <p style="margin: 0; font-family: Arial, sans-serif;">{{ $chat['message_text'] }}</p>
                        </div>
                        <p style="text-align: right; font-size: 16px; font-weight: 400; color: #A0A0A0; margin-top: 8px; font-family: Arial, sans-serif;">{{ $chat['sent_at'] }}</p>
                    </section>
                @endforeach
            @endif
            @if($type == 'new')
                <section style="margin-bottom: 20px;">
                    <p style="margin-top: 20px; font-weight: 600; font-size: 16px; font-family: Arial, sans-serif;">{{ $unansweredMessagesData['guest_name'] }}</p>
                    <div style="border-radius: 6px 6px 0px 6px; padding: 12px; margin: 10px 0; background-color: #ffffff; border: 1px solid #E0E0E0; font-size: 16px; font-weight: 400; line-height: 150%; box-shadow: 0px 2px 11.2px 0px rgba(55, 55, 55, 0.20); font-family: Arial, sans-serif;">
                        <p style="margin: 0; font-family: Arial, sans-serif;">{{ $unansweredMessagesData['message_text'] }}</p>
                    </div>
                    <p style="text-align: right; font-size: 16px; font-weight: 400; color: #A0A0A0; margin-top: 8px; font-family: Arial, sans-serif;">{{ $unansweredMessagesData['sent_at'] }}</p>
                </section>
            @endif
            @if($type == 'test')
                <section style="margin-bottom: 20px;">
                    <p style="margin-top: 20px; font-weight: 600; font-size: 16px; font-family: Arial, sans-serif;">Prueba Guest</p>
                    <div style="border-radius: 6px 6px 0px 6px; padding: 12px; margin: 10px 0; background-color: #ffffff; border: 1px solid #E0E0E0; font-size: 16px; font-weight: 400; line-height: 150%; box-shadow: 0px 2px 11.2px 0px rgba(55, 55, 55, 0.20); font-family: Arial, sans-serif;">
                        <p style="margin: 0; font-family: Arial, sans-serif;">Esto es un mensaje de chat de prueba</p>
                    </div>
                    <p style="text-align: right; font-size: 16px; font-weight: 400; color: #A0A0A0; margin-top: 8px; font-family: Arial, sans-serif;">27 -12 2024</p>
                </section>
            @endif

            <div style="text-align: center; margin-top: 22px;">
                <a href="{{$url}}" class="full-width-button" style="display: inline-block; padding: 12px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 600; width: 100%; height: 45px; box-sizing: border-box; font-family: Arial, sans-serif;">Atender Chat</a>
            </div>
        </div>

        <!-- Footer -->
        @include('components.mails.footer')
    </div>
</body>
</html>
