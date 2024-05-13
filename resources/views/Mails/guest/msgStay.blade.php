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
</head>
<body style="background-image: url('{{ asset("mails/fondo.jpg") }}'); background-size: cover; background-position: center; height: 2048px;font-family: Montserrat;">
    {{-- <div style="display: flex; justify-content: center; align-items: center; height: 100vh; background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('{{ asset("mails/alarma.png") }}'); background-size: cover; background-position: center;">
    </div> --}}
    <div style="display: flex; justify-content: center; align-items: center; text-align: center; height: 100vh; background: linear-gradient(to bottom, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('{{ asset("mails/alarma.png") }}'); background-size: cover; background-position: center;">
        <div style="font-size: 2em; color: #333;">
            <p style="margin: 10rem;">ESCRIBE ALGO</p>
            <p style="margin: 0;">Escribe algo</p>
        </div>
    </div>

    {{-- <span>ESCRIBE ALGO</span> --}}

</body>
</html>
