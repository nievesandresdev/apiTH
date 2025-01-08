<section style="margin-right: 12px; margin-left: 12px" class="responsive-section">
    <div style="border-radius: 3px 3px 50px 3px; background: #F3F3F3; padding: 40px;">
        <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                {{-- mobile image --}}
                <td style="text-align: center;
                           width: 50%;
                           vertical-align: top;
                           padding: 0 0 0 10px;" class="hidden-responsive"> <!-- 10px de padding a la izquierda -->
                    <img src="{{ asset('mails/welcomeafter2.png') }}" alt="Welcome Banner" style="width: 100%; height: 240px; max-width: 100%;object-fit:cover">
                </td>
                <!-- Columna de Texto -->
                <td class="text-content"
                    style="color: #333333;
                           padding: 0 5px 0 0;
                           text-align: left;
                           width: 53%;
                           vertical-align: top;
                           padding-top:22px;">
                    <span style="margin: 0; font-size: 26px; font-style: normal; font-weight: 600; line-height: 110%;display:block">Te damos la bienvenida!</span>
                    {{$after}}
                    @if($after)
                        <p style="margin: 10px 0; font-size: 16px; font-style: normal; font-weight: 400; line-height: 110%; margin-top:12px !important;">
                            Hola {{ $guest_name }}<br>
                            Estamos encantados de darte la bienvenida a la WebApp de {{ $hotel_name }}.<br>
                            Esperamos que hayas disfrutado de tu estancia con nosotros y haberte brindado la atención de calidad que mereces.<br>
                            Deseamos volver a recibirte muy pronto.
                        </p>

                    @else
                        <p style="margin: 10px 0; font-size: 16px; font-style: normal; font-weight: 400; line-height: 110%; margin-top:12px !important;">
                            Hola {{ $guest_name }}<br>
                            Estamos encantados de darte la bienvenida a la WebApp de {{ $hotel_name }}.<br>
                            Esperamos que tu estancia sea única e inolvidable, nuestro equipo está a tu disposición.
                        </p>
                    @endif


                </td>

                <!-- Columna de Imagen -->
                <td style="text-align: center;
                           width: 50%;
                           vertical-align: top;
                           padding: 0 0 0 10px;" class="show-not-responsive"> <!-- 10px de padding a la izquierda -->
                    <img src="{{ asset('mails/welcomeafter1.png') }}" alt="Welcome Banner" style="width: 227px; height: 270px; max-width: 100%;">
                </td>
            </tr>
        </table>
    </div>
</section>
