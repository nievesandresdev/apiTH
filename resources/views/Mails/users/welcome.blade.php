<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: #ffffff;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="max-width: 600px; margin: 0 auto;">
        <tr>
            <td style="background-color: white; padding: 20px; text-align: center;">
                <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
            </td>
        </tr>
        <tr>
            <td style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding: 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="color: white;">
                    <tr>
                        <td style="padding-right: 20px; vertical-align: top;">
                            <h1 style="margin: 0;">¡Bienvenido a Thehoster!</h1>
                            <p style="margin: 10px 0;">[Nombre del usuario] ha creado un usuario en la plataforma para que administres el [tipo de alojamiento] [nombre alojamiento]</p>
                            <p style="margin: 10px 0;">¡Gracias por elegirnos!</p>
                        </td>
                        <td style="text-align: center; vertical-align: top;">
                            <img src="{{ asset('mails/users/banner.png') }}" alt="Welcome Banner" style="max-width: 100%; height: auto;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
