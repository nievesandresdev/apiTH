<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Chat</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

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

        .message-container {
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
            background-color: #ffffff;
            box-shadow: 0px 2px 11.2px 0px rgba(55, 55, 55, 0.20);
        }

        .message-text {
            font-weight: 400;
            margin: 0;
        }

        .message-time {
            text-align: right;
            font-size: 16px;
            font-weight: 400;
            color: #A0A0A0;
            margin-top: 8px;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto;">
        <div style="background-color: white; padding: 20px; text-align: center;">
            <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding: 20px;">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content order-1" style="color: white; padding-right: 40px; text-align: left; width: 50%; vertical-align: top; font-size: 16px; font-weight: 500;">
                        @if($type == 'pending')
                            <h1 style="margin: 0; font-weight: 600;">¡Tienes un chat pendiente!</h1>
                            <p style="margin: 10px 0; font-weight: 400;">Han transcurrido <span style="color: #FFD453;">10 minutos</span> desde que un huésped te ha escrito un mensaje desde el chat</p>
                        @else
                            <h1 style="margin: 0; font-weight: 600;">Tienes un nuevo mensaje en el chat</h1>
                            <p style="margin: 10px 0; font-weight: 400;">Has recibido un nuevo mensaje de un huésped. Puedes ir al mensaje dando click al botón de abajo.</p>
                        @endif
                        <!-- Botón que se muestra en el modo no responsive -->
                        <a href="#" class="full-width-button order-3" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 600; width: 100%; box-sizing: border-box; text-align: center; margin-top: 10px;">Atender Chat</a>
                    </td>
                    <td class="order-2" style="text-align: center; width: 50%; vertical-align: top;">
                        @if($type == 'pending')
                            <img src="{{ asset('mails/chat-pending.png') }}" alt="Chat Image" style="width: 227px; height: 240px;">
                        @else
                            <img src="{{ asset('mails/chat.png') }}" alt="Chat Image" style="width: 227px; height: 240px;">
                        @endif
                        <!-- Botón que se muestra en el modo responsive -->
                        <a href="#" class="full-width-button order-4" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 600; width: 100%; box-sizing: border-box; text-align: center; margin-top: 10px;">Atender Chat</a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Nueva sección añadida aquí -->
        <div style="background-color: white; padding: 20px; text-align: left; margin-top: 24px;">
            <h2 style="margin: 0; font-weight: 600;">Mensaje</h2>
            {{-- <pre>{{ $unansweredMessagesData }}</pre> --}}
            @foreach($unansweredMessagesData as $chat)
                <p style=" margin-top: 20px; font-weight: 600;">{{ $chat['guest_name'] }}</p>
                <div class="message-container">
                    <p class="message-text">{{$chat['message_text']}}</p>
                </div>
                <p class="message-time">{{ $chat['sent_at'] }}</p>
            @endforeach

            <a href="#" class="full-width-button" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 600; width: 100%; box-sizing: border-box; text-align: center; margin-top: 10px;">Atender Chat</a>
        </div>

        <!-- Footer -->
        @include('components.mails.footer')
    </div>
</body>
</html>
