<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <style>
        @media screen and (max-width: 600px) {
            .grid-container {
                display: grid !important;
                grid-template-columns: 1fr !important;
                text-align: center !important;
            }
            .grid-item {
                padding: 10px 0 !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: #ffffff;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto;">
        <div style="background-color: white; padding: 20px; text-align: center;">
            <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding: 20px;">
            <div class="grid-container" style="display: grid; grid-template-columns: 1fr 1fr; align-items: center;">
                <div class="grid-item" style="color: white; padding-right: 40px; text-align: left;">
                    <h1 style="margin: 0;">¡Bienvenido a Thehoster!</h1>
                    <p style="margin: 10px 0;">[Nombre del usuario] ha creado un usuario en la plataforma para que administres el [tipo de alojamiento] [nombre alojamiento]</p>
                    <p style="margin: 10px 0;">¡Gracias por elegirnos!</p>
                </div>
                <div class="grid-item" style="text-align: center;">
                    <img src="{{ asset('mails/users/banner.png') }}" alt="Welcome Banner" style="width: 255px; height: 240px;">
                </div>
            </div>
        </div>
    </div>
</body>
</html>
