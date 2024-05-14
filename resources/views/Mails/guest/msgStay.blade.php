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
    <style>

        /* Media queries para ajustar la imagen de fondo en dispositivos más pequeños */
        @media only screen and (max-width: 600px) {
          body {
            background-size: contain; /* O puedes ajustar con valores específicos como '100% auto' */
            height: 200px; /* Ajusta la altura según el contenido o un valor específico que necesites */
          }
        }
      </style>
</head>
<body style="background-image: url('{{ asset("mails/mail.png") }}'); background-size: cover; height: 6100px; font-family: 'Montserrat', sans-serif;">



</body>
</html>
