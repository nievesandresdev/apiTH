{{-- <p style="color: red">
    {!! $msg !!}
</p> --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    {{-- app.css --}}
    <style>

        /* Media queries para ajustar la imagen de fondo en dispositivos más pequeños */
        @media only screen and (max-width: 600px) {
          body {
            background-size: contain;
            height: 10%;
          }
        }

        @media (max-width: 768px) {
            .welcome-text {
                padding-right: 16px;
            }

            .welcome-text h1 {
                font-size: 30px;
                margin-left: 0;
            }
        }
      </style>
</head>
<body style="background-image: url('{{ asset("mails/fondo.png") }}'); background-size: cover; height: 100%; font-family: 'Montserrat', sans-serif;">
    <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Todo lo que necesitas para optimizar tu estancia, en tu mano. Prueba nuestra WebApp.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    <div class="welcome-text" style="height: 500px; padding: 16px; padding-right: 180px; text-align: right; font-family: 'Montserrat', sans-serif;">
        <h1 style="color: #333; font-size: 48px; margin-left: -20px;">Te damos la <br>bienvenida a <br>[Hotel]</h1>
        {{-- <p style="color: #333; font-size: 16px;">{{ $msg }}</p> --}}
    </div>

    <img src="{{ asset("mails/1.png") }}" alt="1" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/2.png") }}" alt="2" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/3.png") }}" alt="3" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/4.png") }}" alt="4" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/5.png") }}" alt="5" style="display: block; margin: 0 auto;">

    <!-- Texto y QR -->
    <div style="text-align: center; padding: 20px;">
        <p style="color: #333; font-size: 22px; font-family: 'Montserrat', sans-serif;">
            Escanea el código QR o haz click en el botón y empieza a vivir tu viaje como nunca antes
        </p>
        <a href="URL_DE_LA_WEBAPP" style="display: inline-block; padding: 10px 20px; background-color: #f5b700; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px;">Ingresar a WebApp</a>
        <div>
            <img src="{{ asset('mails/qr.png') }}" alt="QR Code" style="display: block; margin: 0 auto;">
        </div>
    </div>

    <!-- Pie de página -->
    <div style="background-color: #333333; color: white; padding: 20px; text-align: center; font-family: 'Montserrat', sans-serif;">
        <p style="font-size: 10px;font-style: normal; font-weight: 200; line-height: normal;">
            Le informamos que ha recibido este correo electrónico en relación con su estancia en nuestro hotel, gestionada a través de una de nuestras plataforma de reservas en línea. Este mensaje tiene como objetivo mejorar su experiencia como cliente y brindarle información relevante sobre nuestros servicios.<br>
            Para cualquier consulta o comentario adicional sobre su reserva o nuestros servicios, le invitamos a contactarnos pulsando <a href="#" style="color: #f5b700; text-decoration: none;">aquí</a> o utilizando los datos de contacto proporcionados en nuestro sitio web.<br>
            Le recordamos que sus datos están siendo tratados de acuerdo con nuestra política de privacidad, la cual puede consultar <a href="#" style="color: #f5b700; text-decoration: none;">aquí</a> o solicitándola a nuestro equipo de atención al cliente.<br>
            [Nombre de empresa hotelera] [Dirección fiscal] [CIF-00000000]
        </p>
    </div>
</body>



</html>
