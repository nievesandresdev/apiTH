<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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

            .div-normal {
                display: none;
            }

            .div-responsive {
                display: block !important;
            }
        }

        .div-responsive {
            display: none;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA; font-family: Arial, sans-serif;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffff;">
        <div style="padding-top: 16px; text-align: center; padding-bottom: 24px;">
            <img src="{{ asset('mails/users/logo.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding: 40px 32px;">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content" style="color: white; text-align: left; width: 50%; vertical-align: top; padding-top: 22px; font-family: Helvetica, Arial, sans-serif;">
                        <span style="margin: 0; font-size: 32px; font-weight: 600; line-height: 110%;">¡Bienvenido a TheHoster!</span>
                        <p style="margin: 12px 0 0 0; font-size: 16px; font-weight: 500; line-height: 110%;">{{$userAuth->name}} te ha creado un usuario en la plataforma para que administres el Hotel</p>
                        <p style="margin: 24px 0 0 0; font-size: 16px; font-weight: 700; line-height: 110%;">¡Gracias por elegirnos!</p>
                    </td>
                    <td style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="{{ asset('mails/users/banner.png') }}" alt="Welcome Banner" style="width: 227px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Sección para pantallas grandes -->
        <div style="background-color: white; padding: 32px; padding-bottom: 64px; text-align: center;" class="div-normal">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr style="height: 100%;">
                    <td style="width: 40%; vertical-align: top; padding-right: 32px;">
                        <img src="{{ asset('mails/users/frame.png') }}" alt="Frame Image" style="width: 100%; height: 100%; border-radius: 8px; object-fit: cover;" class="image-frame">
                    </td>
                    <td class="text-content" style="width: 60%; vertical-align: top; text-align: left; font-family: Verdana, sans-serif;">
                        <h2 style="margin: 0; font-family: Helvetica, Arial, sans-serif;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;">{{ $user->email }}</span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;">{{ $password }}</span></p>
                        <a href="{{ $url }}" style="display: inline-block; padding: 13px 0; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 27px; font-weight: 500; width: 100%; box-sizing: border-box; text-align: center; font-size: 16px; line-height: 110%; font-family: Arial, sans-serif;">Entrar a Thehoster</a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Sección para pantallas pequeñas -->
        <div style="background-color: white; padding: 20px; text-align: center;" class="div-responsive">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr>
                    <td class="text-content order-1" style="width: 60%; vertical-align: top; text-align: left; font-family: Verdana, sans-serif;">
                        <h2 style="margin: 0; font-family: Helvetica, Arial, sans-serif;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;">{{ $user->email }}</span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;">{{ $password }}</span></p>
                    </td>
                    <td class="text-content order-2" style="width: 60%; vertical-align: top;">
                        <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 10px; font-weight: 500; width: 100%; box-sizing: border-box; text-align: center; font-family: Arial, sans-serif;">Entrar a Thehoster</a>
                    </td>
                    <td class="order-3" style="width: 40%; vertical-align: top; padding-right: 20px;">
                        <img src="{{ asset('mails/users/frame.png') }}" alt="Frame Image" style="width: 100%; height: 187px; border-radius: 8px; max-height: 300px;" class="image-frame">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        @include('components.mails.footer', ['showNotify' => false])
    </div>
</body>
</html>
