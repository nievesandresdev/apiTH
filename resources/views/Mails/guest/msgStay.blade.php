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
        @media (max-width: 1024px) {
            div {
                top: 15%;
                left: 10%;
                font-size: 36px;
                width: calc(100% - 20%);
            }
        }
        @media (max-width: 768px) {
            div {
                top: 10%;
                left: 5%;
                font-size: 24px;
                width: calc(100% - 10%);
            }
        }
    </style>
</head>
<body style="background-image: url('{{ asset("mails/fondo.jpg") }}'); background-size: cover; height: 1024px; font-family: 'Montserrat', sans-serif; position: relative;">
    <div style="position: absolute; top: 20%; left: 20%; font-size: 48px; color: #333;">
        Te damos la bienvenida a [Hotel]
    </div>

</body>




</html>
