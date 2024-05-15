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
<body style="background-image: url('{{ asset("mails/fondo.jpg") }}'); background-size: cover; height: 6100px; font-family: 'Montserrat', sans-serif;">
    {{-- <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">Todo lo que necesitas para optimizar tu estancia, en tu mano. Prueba nuestra WebApp.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> --}}
    <div class="welcome-text" style="height: 500px; padding: 16px; padding-right:180px; text-align: right;font-family: 'Montserrat', sans-serif;">
        <h1 style="color: #333; font-size: 48px; margin-left: -20px;">Te damos la <br>bienvenida a <br>[Hotel]</h1>
        {{-- <p style="color: #333; font-size: 16px;">{{ $msg }}</p> --}}
    </div>

    <img src="{{ asset("mails/1.png") }}" alt="1" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/2.png") }}" alt="2" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/3.png") }}" alt="3" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/4.png") }}" alt="4" style="display: block; margin: 0 auto;">
    <img src="{{ asset("mails/5.png") }}" alt="5" style="display: block; margin: 0 auto;">

</body>

</html>
