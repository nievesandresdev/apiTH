<!-- resources/views/Mails/users/recovery-password.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Restablecimiento de contraseña') }}</title>
    <style>
        /* Estilos en línea para correos */
        @media only screen and (max-width: 600px) {
            .full-width-button {
                width: 100% !important;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body style="background-color: #ffffff; font-family: Helvetica, Arial, sans-serif; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #ddd; border-radius: 8px; padding: 20px;">
        <p style="font-size: 24px; font-style: normal; font-weight: 600; line-height: 28px; color: #333; text-align: left;">
            Hola!
        </p>
        <!-- Primer Párrafo -->
        <p style="font-size: 18px; font-style: normal; font-weight: 500; line-height: 28px; color: #333; text-align: left;">
            Has solicitado un enlace para restablecer la contraseña de tu cuenta en TheHoster. Haz click en el siguiente botón para iniciar el proceso.
        </p>

        <!-- Botón Centrado y Adaptable -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.hoster_url') . 'reset-password/' . $token . '?email=' . $notifiable->getEmailForPasswordReset() }}"
               class="full-width-button"
               style="background-color: #FFD453; color: #000; padding: 10px 20px; border-radius: 5px; font-size: 18px; font-weight: 500; text-decoration: none; display: inline-block; width: auto;">
                {{ __('Restablecer contraseña') }}
            </a>
        </div>

        <!-- Segundo Párrafo -->
        <p style="font-size: 18px; font-style: normal; font-weight: 500; line-height: 28px; color: #333; text-align: left;">
            Este enlace de restablecimiento de contraseña expira en 60 minutos.
        </p>

        <p style="font-size: 18px; font-style: normal; font-weight: 500; line-height: 28px; color: #333; text-align: left;">
            Si no solicitaste un restablecimiento de la contraseña, no se requiere ninguna acción.
        </p>

        <!-- Saludo Final -->
        <p style="font-size: 18px; font-style: normal; font-weight: 500; line-height: 28px; color: #333; text-align: left;">
            Saludos,<br>
            TheHoster
        </p>

        <!-- Separación y Separador -->
        <div style="height: 16px;"></div>
        <div style="background: #DADADA; height: 1px; margin-bottom: 16px;"></div>

        <!-- Mensaje de URL alternativa -->
        <p style="font-size: 18px; font-style: normal; font-weight: 500; line-height: 28px; color: #333; text-align: left;">
            Si tienes problemas tras pulsar en el botón “Restablecer contraseña”, copia y pega el enlace en tu navegador para restablecerla:
            <a href="{{ config('app.hoster_url') . 'reset-password/' . $token . '?email=' . $notifiable->getEmailForPasswordReset() }}"
               style="color: #5E7DD4; text-decoration: none; word-wrap: break-word; word-break: break-all;">
                {{ config('app.hoster_url') . 'reset-password/' . $token . '?email=' . $notifiable->getEmailForPasswordReset() }}
            </a>
        </p>
    </div>
</body>
</html>
