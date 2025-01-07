{{-- <p style="color: red">
    {!! $msg !!}
</p> --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>Document</title>
    {{-- app.css --}}
    <style>

        /* Media queries para ajustar la imagen de fondo en dispositivos móviles */
        @media only screen and (max-width: 768px) {
            body {
                background-image: url('{{ asset("mails/fondomobile.png") }}');
            }
        }

        /* Media queries para ajustar la imagen de fondo en dispositivos más pequeños */
        @media only screen and (max-width: 600px) {
          body {
            background-size: contain;
            height: 10%;
          }
        }

        @media (max-width: 768px) {
            .welcome-text {
                padding-right: 3px;
            }

            .welcome-text h1 {
                font-size: 25px;
                margin-left: 0;
            }

            .footer-text {
                font-size: 12px;
            }

            /* Ajustar margen superior en pantallas pequeñas */
            .welcome-message {
                margin-top: 3px;
                padding-left: 16px;
                padding-right: 16px;
                font-size: 20px;
            }
        }
      </style>
</head>
<body style="background-image: url('{{ asset("mails/fondodesktop.png") }}'); background-size: cover; height: 100%; font-family: 'Montserrat'">
    @if ($guest == true)
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">¿Has probado la WebApp de {{ $hotel->name }}? Mira todo lo que hay para explorar!.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    @elseif ($create == true)
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Potencia tu experiencia de viaje, disfruta con nuestra WebApp.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    @else
        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Todo lo que necesitas para optimizar tu estancia, en tu mano. Prueba nuestra WebApp.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    @endif

    <div class="welcome-text" style="height: 500px; padding: 16px; padding-right: 180px; text-align: right; font-family: 'Montserrat'">
        @if ($guest == true)
            <h1 style="color: #333; font-size: 48px; margin-left: -20px;">Échale un<br> vistazo a la <br> WebApp de<br> {{ $hotel->name }}</h1>
        @elseif ($create == true)
            <h1 style="color: #333; font-size: 48px; margin-left: -20px;">Potencia tu<br> experiencia de<br> viaje</h1>
        @else
            <h1 style="color: #333; font-size: 48px; margin-left: -20px;">Te damos la <br>bienvenida a la<br> Webapp</h1>
        @endif
    </div>

    <!-- Nuevo texto -->
    <div class="welcome-message" style="margin-top: 15px; margin-bottom: 35px; padding-left: 40px; padding-right: 40px; font-family: 'Montserrat'">
        <p style="color: #333; font-size: 35px; font-style: normal; font-weight: 500; line-height: normal;">
            {!! $msg !!}
        </p>
    </div>

    <img src="{{ asset("mails/1.png") }}" alt="1" style="display: block; margin: 0 auto; padding: 10px;margin-top: 20px;">
    <h1 style="font-size: 20px;text-align: center;font-family: Montserrat;font-weight: 600;margin-top: 32px;margin-bottom: 64px;">¿Qué encontrarás en nuestra WebApp?</h1>
    <img src="{{ asset("mails/2.png") }}" alt="2" style="display: block; margin: 0 auto; padding: 10px">
    <img src="{{ asset("mails/3.png") }}" alt="3" style="display: block; margin: 0 auto; padding: 10px">
    <img src="{{ asset("mails/4.png") }}" alt="4" style="display: block; margin: 0 auto; padding: 10px">
    <img src="{{ asset("mails/5.png") }}" alt="5" style="display: block; margin: 0 auto; padding: 10px">

    <!-- Texto y QR -->
    <div style="text-align: center; padding: 20px;">
        <p style="color: #333; font-size: 22px; font-family: 'Montserrat', sans-serif;">
                Escanea el código QR o haz click en el botón para volver a la WebApp
        </p>
        <a href="{{ $link }}" target="_blank" style="display: inline-block; padding: 10px 20px; background-color: #f5b700; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px;">Ingresar a WebApp</a>
        <div>
            <img src="{{ asset('mails/qr.png') }}" alt="QR Code" style="display: block; margin: 0 auto;">
        </div>
    </div>

    <!-- Pie de página -->
    <div style="background-color: #333333; color: #F3F3F3; padding: 20px; text-align: center">
        <span class="footer-text" style="font-size: 16px;font-style: normal; font-weight: 100; line-height: 1.1;">
            Le informamos que ha recibido este correo electrónico en relación con su estancia en nuestro alojamiento, 
            gestionada a través de una de nuestras plataforma de reservas en línea.  Conforme a lo establecido en la 
            normativa de Protección de datos, le informamos que los datos facilitados son tratados por (Denominación social del hotel) 
            en calidad de responsable del tratamiento de sus datos, con la finalidad de mejorar su experiencia como cliente y brindarle 
            información relevante sobre nuestros servicios. Puede obtener más información consultando nuestra Política de Privacidad 
            (enlace redirigiendo a Política de privacidad hoster-huésped o enlace para pop up de Política de privacidad hoster-huésped).
        </span>
    </div>
</body>



</html>
