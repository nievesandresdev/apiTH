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
      </style>
</head>
<body style="background-image: url('{{ asset("mails/fondo.png") }}'); background-size: cover; height: 10%; font-family: 'Montserrat', sans-serif;">
    <div style="height: 500px; padding: 10px; text-align: right;font-family: 'Montserrat', sans-serif;">
        <h1 style="color: #333; font-size: 24px;">¡Hola, !</h1>
        <p style="color: #333; font-size: 16px;">{{ $msg }}</p>
    </div>

    <img src="{{ asset("mails/1.png") }}" alt="1">

</body>
</html>
