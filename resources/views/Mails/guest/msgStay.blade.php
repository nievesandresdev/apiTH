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
            td {
                padding-right: 10%;
            }
            div {
                font-size: 36px;
            }
        }
        @media (max-width: 768px) {
            td {
                padding-right: 5%;
            }
            div {
                font-size: 24px;
            }
        }
    </style>
</head>
<body style="background-image: url('{{ asset("mails/fondo.jpg") }}'); background-size: cover; height: 6100px; font-family: 'Montserrat', sans-serif; position: relative;">
    <table style="width: 100%; height: 100%; border-collapse: collapse;">
        <tr>
            <td style="text-align: right; padding-right: 20%; vertical-align: top;">
                <div style="font-size: 48px; color: #333;">
                    Te damos la bienvenida a [Hotel]
                </div>
            </td>
        </tr>
    </table>
    <
</body>



</html>
