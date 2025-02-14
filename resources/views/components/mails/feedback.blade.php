<section style="background: #FFFFFF; border-radius: 3px; margin-left: 16px; margin-right: 36px;">
    @if($currentPeriod == 'in-stay')
        <div style="margin-top: 32px;"></div>
    @endif
    @if($currentPeriod !== 'pre-stay')
    <!-- Título -->
    <div style="">
        <p style="text-align: left; font-family: Roboto, sans-serif; font-size: 20px; font-style: normal; font-weight: 500; line-height: 24px; margin: 0;">
            @if($currentPeriod == 'post-stay' || $after)
                ¿Cómo ha sido tu estancia con nosotros?
            @else
                ¿Qué tal va tu estancia hasta ahora?
            @endif
        </p>
        <!-- Subtítulo -->
        <p style="text-align: left; font-family: Roboto, sans-serif; font-size: 16px; font-style: normal; font-weight: 400; line-height: 24px; margin: 8px 0 0;">
            @if($currentPeriod == 'post-stay' || $after)
                Cuéntanos tu experiencia.
            @else
                Cuéntanos cómo te sientes.
            @endif

        </p>
    </div>



    <!-- Tabla de íconos -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 16px; text-align: center;" class="padding-feedback">
        <tr>
            <!-- Muy Mala -->
            <td style="text-align: center; padding: 0 10px;">
                <a href="{{$webappLinkInbox}}" target="_blank" style="text-decoration: none;">
                    <img src="{{ asset('mails/icons/reviews/VERYWRONG2.png') }}" alt="Muy Mala" style="width: 28px; height: 28px; display: block; margin: 0 auto;">
                    <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Muy Mala</p>
                </a>
            </td>
            <!-- Mala -->
            <td style="text-align: center; padding: 0 10px;">
                <a href="{{$webappLinkInbox}}" target="_blank" style="text-decoration: none;">
                    <img src="{{ asset('mails/icons/reviews/WRONG3.png') }}" alt="Mala" style="width: 28px; height: 28px; display: block; margin: 0 auto;">
                    <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Mala</p>
                </a>
            </td>
            <!-- Normal -->
            <td style="text-align: center; padding: 0 10px;">
                <a href="{{$webappLinkInbox}}" target="_blank" style="text-decoration: none;">
                    <img src="{{ asset('mails/icons/reviews/NORMAL2.png') }}" alt="Normal" style="width: 28px; height: 28px; display: block; margin: 0 auto;">
                    <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Normal</p>
                </a>
            </td>
            <!-- Buena -->
            <td style="text-align: center; padding: 0 10px;">
                <a href="{{$webappLinkInbox}}" target="_blank" style="text-decoration: none;">
                    <img src="{{ asset('mails/icons/reviews/GOOD2.png') }}" alt="Buena" style="width: 28px; height: 28px; display: block; margin: 0 auto;">
                    <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Buena</p>
                </a>
            </td>
            <!-- Muy Buena -->
            <td style="text-align: center; padding: 0 10px;">
                <a href="{{$webappLinkInboxGoodFeel}}" target="_blank" style="text-decoration: none;">
                    <img src="{{ asset('mails/icons/reviews/VERYGOOD2.png') }}" alt="Muy Buena" style="width: 28px; height: 28px; display: block; margin: 0 auto;">
                    <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Muy Buena</p>
                </a>
            </td>
        </tr>
    </table>
    @else
    <h2 style="margin:0;color:#333;font-family: Arial;font-size: 22px;font-weight: 500;">¿Necesitas algo para tu llegada al {{ formatTypeLodging($hotel->type) }}?</h2>
    <p style="margin:0;margin-top: 8px;color: #333;font-family: Arial;font-size: 16px;line-height: 175%;">Cuéntanos cómo recibirte.</p>
    @endif
    <!-- Botón -->
    <div style="max-width:260px;margin:10px auto;">
        {{-- <a href="{{$webappLinkInbox}}" class="response-button" target="_blank" style="color: #F3F3F3;">
            Responder en la WebApp
        </a> --}}
        <a
            href="{{$webappLinkInbox}}"
            target="_blank"
            style="margin:0;border-radius: 6px;background-color: #333;padding: 12px 0;text-align:center;color:#F3F3F3;font-size: 18px;font-weight: 600;line-height: 110%;font-family:Arial;margin-top:16px;display:block;text-decoration: none;width:100%"
        >
            Responder en la WebApp
        </a>
    </div>
</section>
