<table role="presentation" width="100%" cellspacing="0" cellpadding="0"
       style="background: url('{{ asset('mails/rewards/fondo1.png') }}') no-repeat center center; background-size: cover; height: 354px;">
    <tr>
        <td style="height: 354px; vertical-align: middle; text-align: center; padding: 16px;">
            <!-- Tabla interna con dos columnas -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 auto; display: inline-block; text-align: center;">
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
                    <!-- Espacio para el código, ignorado por ahora -->
                    <td colspan="2" style="text-align: center; padding-top: 16px;">
                        <div style="height: 50px;"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
