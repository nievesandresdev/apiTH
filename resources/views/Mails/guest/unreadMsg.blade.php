<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat pendiente</title>
    <style>

        @media only screen and (min-width: 601px) {
            .responsive-table {
                display: block !important;
            }

            .responsive-table-2 {
                display: none !important;
            }
        }
        @media only screen and (max-width: 600px) {
            .responsive-table {
                display: none !important;
            }

            .responsive-table-2 {
                display: block !important;
            }

            .responsive-foot td {
                display: block !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff;">
    <div style="background-color: #fff;">
        <p style="font-family: 'Montserrat';margin:0;padding:24px 0; color: #000;text-align: center;font-size: 28px;font-weight: 600;line-height: 110%;">{{$hotel['name']}}</p>
    </div>
    <div class="responsive-table" style="width: 100%; max-width: 568px; margin: 0 auto;border-radius: 3px 3px 50px 3px;background: #F3F3F3;padding: 44px 40px;">
        <table>
            <tr>
                <td>
                    <div>
                        <h1 style="font-family: 'Montserrat';margin:0;font-size: 26px;font-weight: 600;line-height: 110%;">Tienes un chat pendiente.</h1>
                        <p style="font-family: 'Montserrat';margin:0;font-size: 16px;font-weight: 400;line-height: 150%;margin-top:16px">Tienes un chat pendiente en la WebApp que requiere tu atención. Te recomendamos revisarlo lo antes posible.</p>
                        <div class="align-boton" style="margin-top:32px;"> 
                            <a href="{{$webappLink}}" style="height: 44px;padding: 12px 0;border-radius: 6px;background-color: #333;color: #FFF;text-align: center;font-family: 'Montserrat';font-size: 18px;font-weight: 600;line-height: 110%;box-sizing: border-box;display: block;">
                                Ir a la WebApp
                            </a>
                        </div>
                    </div>
                </td>
                <td>
                    <img class="img-desktop" style="width:200px; height: 236px;margin-left:24px" src="{{ asset('mails/img-desktop.png') }}" alt="">
                </td>
            </tr>
        </table>
    </div>

    <div class="responsive-table-2" style="margin:auto;padding: 40px 24px 48px 24px;border-radius: 3px 3px 50px 3px;background: #F3F3F3;max-width:450px">
        <img class="img-mobile" style="width:100%; height: 168px;" src="{{ asset('mails/img-mobile.png') }}" alt="">
        <div style="margin-top: 24px">
            <h1 style="font-family: 'Montserrat';margin:0;font-size: 24px;font-weight: 600;line-height: 110%;">Tienes un chat pendiente.</h1>
            <p style="font-family: 'Montserrat';margin:0;font-size: 16px;font-weight: 400;line-height: 150%;margin-top:16px">Tienes un chat pendiente en la WebApp que requiere tu atención. Te recomendamos revisarlo lo antes posible.</p>
            <div class="align-boton" style="margin-top:32px;"> 
                <a href="{{$webappLink}}" style="padding: 12px 0;border-radius: 6px;background-color: #333;color: #FFF;text-align: center;font-family: 'Montserrat';font-size: 18px;font-weight: 600;line-height: 110%;box-sizing: border-box;display: block;">
                    Ir a la WebApp
                </a>
            </div>
        </div>
    </div>

    <div style="padding: 48px 24px; max-width:479px; margin:auto">
        <h1 style="margin: 0;font-family: 'Montserrat';font-size: 22px;font-weight: 600;line-height: 150%;">Mensajes pendientes</h1>
        @foreach($unansweredMessagesData as $chat)
        <div style="padding-top:24px;">
            @if($chat['guest_name']) 
            <p style="margin: 0;font-size: 16px;font-family: 'Montserrat';font-style: normal;font-weight: 400;line-height: 150%;">{{ $chat['guest_name'] }}</p>
            @endif
            <div style="padding: 12px;border-radius: 6px 6px 0px 6px;border: 1px solid #BFBFBF;margin-top:4px">
                <p style="margin: 0;font-family: 'Montserrat';font-size: 16px;font-weight: 400;line-height: 150%;">{{ $chat['message_text'] }}</p>
            </div>
            <p style="margin: 0;font-family: 'Montserrat';font-size: 14px;font-weight: 400;line-height: 140%;color:#A0A0A0;margin-top:8px;text-align: right;">{{ $chat['sent_at'] }}</p>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 16px;margin-bottom: 64px; padding:0 24px">
        <h1 style="font-family: 'Montserrat';margin:0;font-size: 22px;font-weight: 600;line-height: 150%;text-align: center;">Responde al mensaje</h1>
        <div style="max-width:355px;margin:auto;padding: 32px 32px 40px 32px;background: #F3F3F3; border-radius: 10px;margin-top:24px">
            <p style="font-family: 'Montserrat';margin:0;color: #000;text-align: center;font-size: 14px;font-weight: 500;line-height: 150%; ">Escanea el código QR o dale al botón</p>
            {{-- <div style="width: 170px;height: 170px;margin:auto;border-radius: 7.673px;box-shadow: 0px 3.5px 13.1px 2px rgba(73, 73, 73, 0.30);margin-top:24px;"> --}}
            <img style="width: 170px;height: 170px;margin:auto;display:block" src="{{ asset('mails/qr_url_generica 2.png') }}" alt="">
            {{-- </div> --}}
            <div style="text-align: center; margin-top:40px;">
                <a href="{{$webappLink}}" style="padding: 12px 0;border-radius: 6px;background-color: #333;color: #FFF;text-align: center;font-family: 'Montserrat';font-size: 18px;font-weight: 600;line-height: 110%;display: block;box-sizing: border-box;">
                    Ir a la WebApp
                </a>
            </div>
        </div>
    </div>
    

    <div style="padding: 24px 32px;background-color:#333">
        <table  class="responsive-foot" style="width: 100%">
            <tr>
                <td><p class="text-footer" style="font-family: 'Montserrat';font-size: 14px;font-weight: 400;line-height: 150%;color:#fff">Av. de Velázquez, 212, Churriana, 29004 Málaga</p></td>
                <td style="width: 107px">
                    <div>
                        <h3 class="footer-w-text" style="margin: 0;font-family: 'Montserrat';font-size: 16px;font-weight: 600;line-height: 150%;color:#fff">Redes sociales</h3>
                        <div style="display: flex;justify-content:center;margin-top:12px">
                            <img style="margin-right: 6px" src="{{ asset('mails/ri_instagram-line.png') }}" alt="instagram icon">
                            <img style="margin-left: 6px" src="{{ asset('mails/web.png') }}" alt="web icon">
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="margin:24px auto;width:312px">
            <table style="width: 100%">
                <tr>
                    <td style="padding-right: 8px"><p style="margin:0;color: #A0A0A0;font-family: 'Montserrat';font-size: 12px;font-weight: 400;line-height: 150%;text-align:right">Política de privacidad</p></td>
                    <td style="padding-left: 8px"><p style="margin:0;color: #A0A0A0;font-family: 'Montserrat';font-size: 12px;font-weight: 400;line-height: 150%;text-align:left">Cancelar suscripción</p></td>
                </tr>
            </table>
        </div>
    </div>
    <div style="padding: 24px 40px;">
        <p style="text-align: center;font-family: 'Montserrat';margin:0;color: #6E6E6E;font-size: 10px;font-weight: 400;line-height: 154%;">Ha recibido este correo electrónico en relación con su estancia en nuestro alojamiento. Este mensaje tiene como objetivo mejorar su experiencia como cliente y brindarle información relevante sobre nuestros servicios. Para cualquier consulta o comentario adicional sobre su reserva o nuestros servicios, puede responder directamente a este correo o utilizar los datos de contacto proporcionados en nuestro sitio web. Le recordamos que sus datos están siendo tratados de acuerdo con nuestra <span style="font-weight: 600">política de privacidad</span>.</p>
    </div>
</body>
</html>
