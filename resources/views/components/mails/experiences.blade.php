<div style="padding: 0 40px;">
    <!-- Línea horizontal -->
    <hr style="border: none; border-top: 1px solid #E0E0E0; margin: 32px 0;">

    <!-- Título -->
    <h2 style="color: #333; font-family: Roboto, sans-serif; font-size: 22px; font-style: normal; font-weight: 500; line-height: 32px; text-align: left; margin-bottom: 8px;">
        Estos destinos te estarán esperando
    </h2>

    <!-- Sección con 3 Cards (Visible solo en escritorio) -->
    <section class="desktop-only" style="margin: 12px 0; background: #FFFFFF; display: table; width: 100%;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-spacing: 12px 0; text-align: left;">
            <tr>
                <!-- Card 1 -->
                <td style="width: 150px; border: 1px solid #F3F3F3; border-radius: 4px; overflow: hidden; background: #FFFFFF; padding: 0;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="padding: 0;">
                                <img src="{{ $data[0]['image']['name'] }}" alt="{{ $data[0]['title'] }}" style="width: 100% !important; height: 120px; display: block;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; text-align: left;">
                                <p style="color: #333; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin: 0; white-space: normal; word-break: break-word; line-height: 1.5;">
                                    {{ $data[0]['title'] }}
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; text-align: left;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: auto;">
                                    <tr>
                                        <td style="padding-right: 4px;">
                                            <img src="{{ asset('mails/WA.star.png') }}" alt="Estrella" style="width: 16px; height: 16px; display: inline-block;">
                                        </td>
                                        <td>
                                            <span style="color: #333; font-family: Roboto, sans-serif; font-size: 16px; font-weight: 700;">5.0</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; text-align: right;">
                                <a href="#" style="font-family: Roboto, sans-serif; font-size: 12px; text-decoration: underline; color: #333;">Ver en la WebApp</a>
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- Card 2 -->
                <td style="width: 150px; border: 1px solid #F3F3F3; border-radius: 4px; overflow: hidden; background: #FFFFFF; padding: 0;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="padding: 0;">
                                <img src="{{ $data[1]['image']['name'] }}" alt="{{ $data[1]['title'] }}" style="width: 100% !important; height: 120px; display: block;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; text-align: left;">
                                <p style="color: #333; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin: 0; white-space: normal; word-break: break-word; line-height: 1.5;">
                                    {{ $data[1]['title'] }}
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; text-align: left;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: auto;">
                                    <tr>
                                        <td style="padding-right: 4px;">
                                            <img src="{{ asset('mails/WA.star.png') }}" alt="Estrella" style="width: 16px; height: 16px; display: inline-block;">
                                        </td>
                                        <td>
                                            <span style="color: #333; font-family: Roboto, sans-serif; font-size: 16px; font-weight: 700;">5.0</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; text-align: right;">
                                <a href="#" style="font-family: Roboto, sans-serif; font-size: 12px; text-decoration: underline; color: #333;">Ver en la WebApp</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </section>

    <!-- Sección con 2 Cards (Visible solo en móvil) -->
    <section class="mobile-only" style="margin: 12px 0; background: #FFFFFF; display: none;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-spacing: 12px 0; text-align: left;">
            <tr>
                <!-- Card 1 -->
                <td style="width: 100%; border: 1px solid #F3F3F3; border-radius: 4px; overflow: hidden; background: #FFFFFF; padding: 0;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td style="padding: 0;">
                                <img src="https://imagen.research.google/main_gallery_images/a-brain-riding-a-rocketship.jpg" alt="Catedral de Sevilla" style="width: 100%; max-width: 150px; height: 120px; display: block;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; text-align: left;">
                                <p style="color: #333; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin: 0; white-space: normal; word-break: break-word; line-height: 1.5;">
                                    Catedral de Sevilla
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; text-align: left;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: auto;">
                                    <tr>
                                        <td style="padding-right: 4px;">
                                            <img src="{{ asset('mails/WA.star.png') }}" alt="Estrella" style="width: 16px; height: 16px; display: inline-block;">
                                        </td>
                                        <td>
                                            <span style="color: #333; font-family: Roboto, sans-serif; font-size: 16px; font-weight: 700;">5.0</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; text-align: right;">
                                <a href="#" style="font-family: Roboto, sans-serif; font-size: 12px; text-decoration: underline; color: #333;">Ver en la WebApp</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </section>
</div>
