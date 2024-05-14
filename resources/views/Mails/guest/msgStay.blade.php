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
          h2 {
            font-size: 85px;
          }
        }
      </style>
</head>
<body style="background-image: url('{{ asset("mails/fondo.jpg") }}'); background-size: cover; height: 6100px; font-family: 'Montserrat', sans-serif;">
    <h2>Hola</h2>
</body>
</html>
