<section style="" class="responsive-section">
    <div class="hero-section-postcheckin" style="border-radius: 3px 3px 50px 3px; background: #F3F3F3;">
        <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                {{-- mobile image --}}
                <td style="text-align: center;width: 50%;vertical-align: middle;padding: 0 0 0 10px;" class="hidden-responsive">
                    <img src="{{ asset('mails/rewards/header2.png') }}" alt="header1" style="width: 100%; height: 240px; max-width: 100%;object-fit:cover">
                </td>
                <!-- Columna de Texto -->
                <td class="text-content" style="color: #333333;text-align: left;vertical-align: middle;padding-right:20px; padding-top: 20px;">
                    <h1 style="margin: 0;color: #333;font-size: 26px;font-weight: 600;line-height: 110%;">¡Enhorabuena!</h1>
                    <p style="margin: 0;color: #333;font-size: 16px;font-weight: 400;line-height: 28px;margin-top:14px">
                        Hola {{ $rewardStay->guest->name }}
                        <br>
                        uno de tus referidos ha utilizado tu código de descuento...
                        <br>
                        ¿Sabes lo que significa eso? ¡Un regalo para ti!
                    </p>
                </td>

                <!-- Columna de Imagen -->
                <td style="text-align: center;vertical-align: middle;width: 212px;padding: 0 0 0 10px;" class="show-not-responsive">
                    <img src="{{ asset('mails/rewards/header1.png') }}" alt="header2" style="width: 208px; height: 254px; max-width: 100%;">
                </td>
            </tr>
        </table>
    </div>
</section>
