<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <style>
        @media only screen and (max-width: 600px) {
            body {
                background-color: #ffffff !important;
            }
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
            .full-width-button {
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }
            .image-frame {
                height: auto !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #FAFAFA;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; font-family: 'Montserrat', sans-serif;">
        <div style="background-color: white; padding: 20px; text-align: center;">
            <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding: 20px;">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content" style="color: white; padding-right: 40px; text-align: left; width: 50%; vertical-align: top; font-family: 'Montserrat', sans-serif;">
                        <h1 style="margin: 0;">¡Bienvenido a Thehoster!</h1>
                        <p style="margin: 10px 0;">[Nombre del usuario] ha creado un usuario en la plataforma para que administres el [tipo de alojamiento] [nombre alojamiento]</p>
                        <p style="margin: 10px 0;">¡Gracias por elegirnos!</p>
                    </td>
                    <td style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="{{ asset('mails/users/banner.png') }}" alt="Welcome Banner" style="width: 227px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Nueva sección añadida aquí -->
        <div style="background-color: white; padding: 20px; text-align: center; margin-top: 64px;">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr>
                    <td style="width: 40%; vertical-align: top; padding-right: 20px;">
                        <img src="{{ asset('mails/users/frame.png') }}" alt="Frame Image" style="width: 100%; height: 187px; border-radius: 8px; max-height: 300px;" class="image-frame">
                    </td>
                    <td class="text-content" style="width: 60%; vertical-align: top; text-align: left; font-family: 'Montserrat', sans-serif;">
                        <h2 style="margin: 0;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;">[mail@mail.com]</span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;">[wef9123sdjinql]</span></p>
                        <a href="#" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 10px; font-weight: 600; width: 100%; box-sizing: border-box; text-align: center;">Entrar a Thehoster</a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div style="background-color: #1A1A1A; padding: 20px; text-align: center; color: #ffffff; font-family: 'Montserrat', sans-serif; margin-top: 64px;">
            <img src="{{ asset('mails/logo-white.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto 20px;">
            <div style="margin-bottom: 20px;">
                <a href="#" style="margin: 0 10px; display: inline-block;">
                    <img src="{{ asset('mails/linkedin.png') }}" alt="LinkedIn" style="width: 24px; height: 24px;">
                </a>
                <a href="#" style="margin: 0 10px; display: inline-block;">
                    <img src="{{ asset('mails/web.png') }}" alt="Website" style="width: 24px; height: 24px;">
                </a>
            </div>
            <div style="margin-bottom: 12px;">
                <a href="#" style="color: #A0A0A0; text-decoration: none; margin-right: 15px; font-weight: 400; font-size: 14px;">Aviso legal</a>
                <a href="#" style="color: #A0A0A0; text-decoration: none; font-weight: 400; font-size: 14px;">Política de privacidad</a>
            </div>
            <p style="margin: 12px 0 0; font-size: 14px;">© Copyright TheHoster 2024</p>
        </div>
        <div style="background-color: #ffffff; padding: 20px; text-align: center; color: #A0A0A0; font-family: 'Montserrat', sans-serif;">
            <p style="margin: 0;">Nota: Este correo ha sido enviado desde una dirección de e-mail que no acepta correos entrantes. Por favor, no respondas a este e-mail.</p>
        </div>
    </div>
</body>
</html>
