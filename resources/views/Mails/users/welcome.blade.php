<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <style>
        @media only screen and (max-width: 600px) {
            .responsive-table, .responsive-table-2 {
                width: 100% !important;
                display: block !important;
            }
            .responsive-table td, .responsive-table-2 td {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                padding: 10px 0 !important;
            }
            .responsive-table .text-content, .responsive-table-2 .text-content {
                text-align: left !important;
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
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content" style="color: white; padding-right: 40px; text-align: left; width: 50%; vertical-align: top;">
                        <h1 style="margin: 0;">¡Bienvenido a Thehoster!</h1>
                        <p style="margin: 10px 0;">[Nombre del usuario] ha creado un usuario en la plataforma para que administres el [tipo de alojamiento] [nombre alojamiento]</p>
                        <p style="margin: 10px 0;">¡Gracias por elegirnos!</p>
                    </td>
                    <td style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="{{ asset('mails/users/banner.png') }}" alt="Welcome Banner" style="width: 255px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Nueva sección añadida aquí -->
        <div style="background-color: white; padding: 20px; text-align: center;">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                        <img src="{{ asset('mails/users/frame.png') }}" alt="Frame Image" style="width: 100%; height: auto; border-radius: 8px;">
                    </td>
                    <td class="text-content" style="width: 50%; vertical-align: top; text-align: left;">
                        <h2 style="margin: 0;">Datos de acceso</h2>
                        <p style="margin: 10px 0;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> [mail@mail.com]</p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> [wef9123sdjinql]</p>
                        <a href="#" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 10px;">Entrar a Thehoster</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
