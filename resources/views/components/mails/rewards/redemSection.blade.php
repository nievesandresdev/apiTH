<table role="presentation" width="100%" cellspacing="0" cellpadding="0"
       style="background: url('{{ asset('mails/rewards/fondo1.png') }}') no-repeat center center; background-size: cover; height: 354px; font-family: Roboto, sans-serif; margin-top: 32px;">
    <tr>
        <td style="height: 354px; vertical-align: middle; text-align: center; padding: 16px;">
            <!-- Tabla interna con contenido, ancho fijo -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 auto; text-align: center; max-width: 400px; width: 100%;">
                <tr>
                    <!-- Columna: Imagen del regalo -->
                    <td style="width: 50%; text-align: right; padding-right: 8px; vertical-align: middle;">
                        <img src="{{ asset('mails/rewards/regalo.png') }}" alt="Regalo"
                             style="max-height: 150px; display: block; margin: 0 auto;">
                    </td>

                    <!-- Columna: Texto del descuento -->
                    <td style="width: 50%; text-align: left; padding-left: 8px; vertical-align: middle;">
                        <h2 style="margin: 0; color: #fff; line-height: 145.455%;">
                            <span class="discount-percentage" style="font-size: 61px; font-weight: 600; display: block;">{{ $rewardStay->reward->reward_amount }}</span>
                            <span class="discount-text" style="font-size: 26px; font-weight: 400; display: block; margin-top: 16px;">de descuento</span>
                        </h2>
                    </td>
                </tr>
                <tr>
                    <!-- Fila: Título "TU CÓDIGO" -->
                    <td colspan="2" style="text-align: center; padding-top: 8px;">
                        <div style="font-size: 14px; font-weight: 600; color: #FFFFFF;">
                            TU CÓDIGO
                        </div>
                    </td>
                </tr>
                <tr>
                    <!-- Fila: Código de regalo -->
                    <td colspan="2" style="text-align: center; padding-top: 4px;">
                        <table role="presentation" cellspacing="0" cellpadding="0"
                               style="margin: 0 auto; display: table; border-collapse: separate; border-radius: 12px; overflow: hidden; border: 2px solid #333333; width: 400px;">
                            <tr>
                                <td style="background-color: #fff; color: #000; font-size: 24px; font-weight: 400; text-align: center; word-wrap: break-word;
                                           width: 400px; height: 48px; line-height: 48px; vertical-align: middle; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $rewardStay->reward->code }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
