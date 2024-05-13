{{-- <p style="color: red">
    {!! $msg !!}
</p> --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-image: url('{{ asset("mails/fondo.jpg") }}');
            background-size: cover;
            background-position: center;
            height: auto;
        }
        /* Estilos adicionales para el contenido del correo */
    </style>
</head>
<body style="background-image: url('{{ asset("mails/fondo.jpg") }}')">
    <p>Hola {{ asset("mails/fondo.jpg") }},</p>
    <img src="{{ asset('mails/logo.png') }}" alt="Logo de la empresa">
    {!! $msg !!}
</body>
</html>

