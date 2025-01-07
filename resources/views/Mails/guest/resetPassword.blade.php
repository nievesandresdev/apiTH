<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reset password</title>
    <style>
        .hotel-name{
            color: #333;
            text-align: center;
            font-family: Arial;
            font-size: 40px;
            font-weight: 600;
            line-height: 50px;
        }

        .title{
            color: #333;
            text-align: center;
            font-family: Arial;
            font-style: normal;
            font-weight: 500;
            line-height: 32px;
        }
        
        .salute{
            color: #333;
            font-family: Arial;
            font-style: normal;
            font-weight: 600;
            line-height: 28px;
        }

        .msg{
            color: #333;
            font-family: Arial;
            font-size: 18px;
            font-style: normal;
            font-weight: 500;
            line-height: 28px; /* 155.556% */
        }

        a{  
            display: block;
            box-sizing: border-box;
            width: 200px;
            height: 40px;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid #FFF;
            background: #333;
            box-shadow: 0px 3px 8px 0px rgba(0, 0, 0, 0.12), 0px 3px 1px 0px rgba(0, 0, 0, 0.04);

            color: #FAFAFA !important;
            text-align: center;
            font-family: Arial;
            font-size: 14px;
            font-style: normal;
            font-weight: 700;
            line-height: 16px; /* 114.286% */
            text-decoration: none;
        }

        @media only screen and (min-width: 601px) {
            .hotel-name{
                font-size: 40px;
            }   
            .title{
                font-size: 30px;
            }
            .salute{
                font-size: 24px;
            }
        }
        @media only screen and (max-width: 600px) {
            .hotel-name{
                font-size: 30px;
            }
            .title{
                font-size: 24px;
            }
            .salute{
                font-size: 20px;
            }   
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
   
    <div style="margin: 40px auto;max-width:600px;padding:0 32px;">
        @if ($hotel)
            <h1 class="hotel-name" style="margin: 0">{{ $hotel['name'] }}</h1>
        @endif
        <p class="title" style="margin: 0;margin-top:32px;">Restablecer contraseña</p>
        <p class="salute"  style="margin: 0;margin-top:40px;">Hola {{ $guest['name']}}!</p>

        <p class="msg"  style="margin: 0;margin-top:16px;">
            Has solicitado un enlace para restablecer la contraseña de tu cuenta en nuestra WebApp. Haz click en el siguiente botón para iniciar el proceso.
        </p>
        <div style="margin: 0;margin-top:32px;">
            <a href="{{$url}}">
                Restablecer contraseña
            </a>
        </div>
    </div>
</body>
</html>
