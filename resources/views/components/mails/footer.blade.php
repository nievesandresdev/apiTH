@php
    $url = config('app.hoster_url');
@endphp
<div style="background-color: #1A1A1A; padding: 32px; text-align: center; color: #ffffff;">
    <img src="{{ asset('mails/logo-white.png') }}" alt="Thehoster Logo" style="display: block; margin: 0 auto 20px;">

    <!-- New icons for web and LinkedIn -->
    <div style="margin-bottom: 20px;">
        <a href="https://thehoster.io/" target="_blank" style="margin-right: 16px;">
            <img src="{{ asset('mails/web.png') }}" alt="Website Icon" style="width: 24px; height: 24px;">
        </a>
        <a href="https://www.linkedin.com/company/the-hoster/" target="_blank">
            <img src="{{ asset('mails/linkedin.png') }}" alt="LinkedIn Icon" style="width: 24px; height: 24px;">
        </a>
    </div>

    <div class="responsive-footer-links" style="margin-bottom: 24px; margin-top:16px;">
        <a href="https://thehoster.io/aviso-legal" style="color: #A0A0A0; text-decoration: none; font-size: 12px; font-style: normal; font-weight: 400; line-height: 150%; margin-right: 15px;">Aviso legal</a>
        <a href="https://thehoster.io/privacidad" style="color: #A0A0A0; text-decoration: none; font-size: 12px; font-style: normal; font-weight: 400; line-height: 150%; margin-right: 15px;">Política de privacidad</a>
        @if($showNotify)
            <a href="{{$url}}equipo/configuracion/usuarios?n={{$showNotify}}" class="config-notifications" style="color: #A0A0A0; text-decoration: none; font-size: 12px; font-style: normal; font-weight: 400; line-height: 150%;">Configurar notificaciones</a>
        @endif
    </div>
    <p style="margin: 12px 0 0; font-size: 12px; font-style: normal; font-weight: 400; line-height: 150%;">© Copyright TheHoster 2024</p>
</div>

<div style="background-color: #ffffff; padding: 20px; text-align: center; color: #A0A0A0;">
    <p style="margin: 0; font-size: 12px; font-style: normal; font-weight: 400; line-height: 150%;">Nota: Este correo ha sido enviado desde una dirección de e-mail que no acepta correos entrantes. Por favor, no respondas a este e-mail.</p>
</div>

<style>
    @media only screen and (max-width: 600px) {
        .responsive-footer-links {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .responsive-footer-links a {
            font-size: 12px;
            font-style: normal;
            font-weight: 400;
            line-height: 150%;
            margin: 5px 0;
        }
        .responsive-footer-links .config-notifications {
            margin-top: 15px;
        }
    }
</style>
