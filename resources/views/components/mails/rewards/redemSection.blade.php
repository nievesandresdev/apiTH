<table role="presentation" width="100%" cellspacing="0" cellpadding="0"
       style="background: url('{{ asset('mails/rewards/fondo1.png') }}') no-repeat center center; background-size: cover; height: 354px;">
    <tr>
        <td style="height: 354px; vertical-align: middle; text-align: center; padding: 16px;">
            <!-- Tabla interna con contenido -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 auto; text-align: center;">
                <tr>
                    <!-- Columna: Imagen del regalo -->
                    <td style="width: 50%; text-align: right; padding-right: 8px; vertical-align: middle;">
                        <img src="{{ asset('mails/rewards/regalo.png') }}" alt="Regalo"
                             style="max-height: 150px; display: block; margin: 0 auto;">
                    </td>

                    <!-- Columna: Texto del descuento -->
                    <td style="width: 50%; text-align: left; padding-left: 8px; vertical-align: middle;">
                        <h2 style="margin: 0; font-family: Arial, sans-serif; color: #fff; line-height: 145.455%;">
                            <span class="discount-percentage" style="font-size: 64px; font-weight: 800; display: block;">35%</span>
                            <span class="discount-text" style="font-size: 32px; font-weight: 400; display: block; margin-top: 16px;">de descuento</span>
                        </h2>
                    </td>
                </tr>
                <tr>
                    <!-- Fila: Título "TU CÓDIGO" -->
                    <td colspan="2" style="text-align: center; padding-top: 8px;">
                        <div style="font-family: Arial, sans-serif; font-size: 14px; font-weight: 600; color: #000;">
                            TU CÓDIGO
                        </div>
                    </td>
                </tr>
                <tr>
                    <!-- Fila: Código de regalo -->
                    <td colspan="2" style="text-align: center; padding-top: 4px;">
                        <table role="presentation" cellspacing="0" cellpadding="0"
                               style="margin: 0 auto; display: inline-block; border-collapse: separate; border-radius: 12px; overflow: hidden; border: 2px solid #000;">
                            <tr>
                                <td style="background-color: #fff; color: #000; font-family: Arial, sans-serif; font-size: 24px; font-weight: 400; padding: 12px 24px; text-align: center; border-radius: 12px;">
                                    HOTELESPRUEBA2545
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
