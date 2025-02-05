<!-- Sección para Desktop -->
<table class="desktop-section" role="presentation" width="100%" cellspacing="0" cellpadding="0"
       style="background: url('{{ asset('mails/rewards/fondo1.png') }}') no-repeat center center; background-size: cover; height: 354px;">
    <tr>
        <td style="padding: 16px; height: 354px; vertical-align: middle; text-align: center;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 16px;">
                <!-- Imagen del regalo -->
                <img src="{{ asset('mails/rewards/regalo.png') }}" alt="Regalo" style="max-height: 150px; display: block;">

                <!-- Texto de descuento -->
                <div style="text-align: left;">
                    <h2 style="margin:0;color: #fff; font-family: Arial, sans-serif; font-size: 32px; font-weight: 700; line-height: 145.455%;">
                        35%<br>de descuento
                    </h2>
                </div>
            </div>
        </td>
    </tr>
</table>

<!-- Sección para Mobile -->
<table class="mobile-section" role="presentation" width="100%" cellspacing="0" cellpadding="0"
       style="background: url('{{ asset('mails/rewards/fondo2.png') }}') no-repeat center center; background-size: cover; height: 354px; display: none;">
    <tr>
        <td style="padding: 16px; height: 354px; vertical-align: middle; text-align: center;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                <!-- Imagen del regalo (más pequeña) -->
                <img src="{{ asset('mails/rewards/regalo.png') }}" alt="Regalo" style="max-height: 120px; display: block;">

                <!-- Texto de descuento (más pequeño) -->
                <div style="text-align: left;">
                    <h2 style="margin:0;color: #fff; font-family: Arial, sans-serif; font-size: 24px; font-weight: 700; line-height: 145.455%;">
                        35%<br>de descuento
                    </h2>
                </div>
            </div>
        </td>
    </tr>
</table>


