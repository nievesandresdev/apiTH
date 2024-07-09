<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Feedback</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap');

        body {
            font-family: 'Montserrat', sans-serif;
        }

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
<body style="margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; background-color: #FAFAFA;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; font-family: 'Montserrat', sans-serif;">
        <div style="background-color: white; padding: 20px; text-align: center;">
            <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding: 20px;">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content" style="color: white; padding-right: 40px; text-align: left; width: 50%; vertical-align: top; font-family: 'Montserrat', sans-serif; font-size: 16px; font-style: normal; font-weight: 500;">
                        <h1 style="margin: 0;">¡Feedback pendiente de respuesta!</h1>
                        <p style="margin: 10px 0;">Un huésped ha brindado un feedback acerca de su experiencia en tu [tipo de alojamiento] [nombre alojamiento]</p>
                        <a href="#" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; width: 100%; box-sizing: border-box; text-align: center; margin-top: 10px;">Atender Feedback</a>
                    </td>
                    <td style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="{{ asset('mails/feedback.png') }}" alt="Feedback Image" style="width: 227px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        @include('components.mails.footer')
    </div>
</body>
</html>
