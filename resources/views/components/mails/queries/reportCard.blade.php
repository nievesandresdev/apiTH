<div>
    <h4 style="margin: 0; font-size: 16px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif;margin-top:{{$period == 'POST-STAY' ? '24px' : '0px'}}">{{$period}} - Resumen de Seguimiento</h4>
    <span style="display: block; font-size: 10px; font-style: italic; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; color: #666666;">
        {{ $stats['total'] }} {{ $stats['total'] > 1 || $stats['total'] === 0 ? 'RESPUESTAS' : 'RESPUESTA' }}
    </span>
    <div class="summary-card" style="margin-top: 4px;border-radius: 5.638px 5.638px 5.638px 0px;border: 2px solid #0B6357;background: #FFF;">
        <table style="margin: 0;width: 100%;">
            <tr>
                <!-- Muy Buena -->
                <td style="text-align: center;width: 20%;">
                    <img src="{{ asset('mails/icons/reviews/VERYGOOD2.png') }}" alt="Muy Buena" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                    <h2 style="font-size: 14px; font-weight: 700; line-height: 110%; font-family: 'Roboto', sans-serif; margin: 0;text-align: center;margin-top: 4px;">{{$stats['breakdown'][0]['percent']}}%</h2>
                    <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;text-align: center;margin-top: 4px;">Muy Bueno</p>
                    <table style="margin: auto;">
                        <tr>
                            <td><img src="{{ asset('mails/icons/WA.huespedes.png') }}" alt="Muy Buena" style="width: 16px; height: 16px; display: block; margin: 0 auto;"></td>
                            <td style="padding-top: 4px;">
                                <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;font-style: italic;color:#666;">{{$stats['breakdown'][0]['count']}}</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- Buena -->
                <td style="text-align: center;width: 20%;">
                    <img src="{{ asset('mails/icons/reviews/GOOD2.png') }}" alt="Buena" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                    <h2 style="font-size: 14px; font-weight: 700; line-height: 110%; font-family: 'Roboto', sans-serif; margin: 0;text-align: center;margin-top: 4px;">{{$stats['breakdown'][1]['percent']}}%</h2>
                    <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;text-align: center;margin-top: 4px;">Bueno</p>
                    <table style="margin: auto;">
                        <tr>
                            <td><img src="{{ asset('mails/icons/WA.huespedes.png') }}" alt="Muy Buena" style="width: 16px; height: 16px; display: block; margin: 0 auto;"></td>
                            <td style="padding-top: 4px;"><p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;font-style: italic;color:#666;">{{$stats['breakdown'][1]['count']}}</p></td>
                        </tr>
                    </table>
                </td>
                <!-- Normal -->
                <td style="text-align: center;width: 20%;">
                    <img src="{{ asset('mails/icons/reviews/NORMAL2.png') }}" alt="Normal" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                    <h2 style="font-size: 14px; font-weight: 700; line-height: 110%; font-family: 'Roboto', sans-serif; margin: 0;text-align: center;margin-top: 4px;">{{$stats['breakdown'][2]['percent']}}%</h2>
                    <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;text-align: center;margin-top: 4px;">Normal</p>
                    <table style="margin: auto;">
                        <tr>
                            <td><img src="{{ asset('mails/icons/WA.huespedes.png') }}" alt="Muy Buena" style="width: 16px; height: 16px; display: block; margin: 0 auto;"></td>
                            <td style="padding-top: 4px;"><p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;font-style: italic;color:#666;">{{$stats['breakdown'][2]['count']}}</p></td>
                        </tr>
                    </table>
                </td>
                <!-- Mala -->
                <td style="text-align: center;width: 20%;">
                    <img src="{{ asset('mails/icons/reviews/WRONG3.png') }}" alt="Mala" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                    <h2 style="font-size: 14px; font-weight: 700; line-height: 110%; font-family: 'Roboto', sans-serif; margin: 0;text-align: center;margin-top: 4px;">{{$stats['breakdown'][3]['percent']}}%</h2>
                    <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;text-align: center;margin-top: 4px;">Malo</p>
                    <table style="margin: auto;">
                        <tr>
                            <td><img src="{{ asset('mails/icons/WA.huespedes.png') }}" alt="Muy Buena" style="width: 16px; height: 16px; display: block; margin: 0 auto;"></td>
                            <td style="padding-top: 4px;"><p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;font-style: italic;color:#666;">{{$stats['breakdown'][3]['count']}}</p></td>
                        </tr>
                    </table>
                </td>
                <!-- Muy Mala -->
                <td style="text-align: center;width: 20%;">
                    <img src="{{ asset('mails/icons/reviews/VERYWRONG2.png') }}" alt="Muy Mala" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                    <h2 style="font-size: 14px; font-weight: 700; line-height: 110%; font-family: 'Roboto', sans-serif; margin: 0;text-align: center;margin-top: 4px;">{{$stats['breakdown'][4]['percent']}}%</h2>
                    <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;text-align: center;margin-top: 4px;">Muy Malo</p>
                    <table style="margin: auto;">
                        <tr>
                            <td><img src="{{ asset('mails/icons/WA.huespedes.png') }}" alt="Muy Buena" style="width: 16px; height: 16px; display: block; margin: 0 auto;"></td>
                            <td style="padding-top: 4px;"><p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;font-style: italic;color:#666;">{{$stats['breakdown'][4]['count']}}</p></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @if($stats['comments_count'] > 0)
        <div style="border-radius: 0px 0px 5.638px 5.638px;background: #0B6357;padding: 2.819px 5.638px;display:inline-block;">
            <table>
                <tr>
                    <td><img src="{{ asset('mails/icons/1.TH.chat-fill.png') }}" alt="Muy Buena" style="width: 12px; height: 12px; display: block;margin-right: 2.82px;"></td>
                    <td style="padding-top: 2px;">
                        <p style="font-size: 10px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif; ; margin: 0;font-style: italic;color:#ffffff;">Incluye {{$stats['comments_count']}} comentario{{$stats['comments_count'] > 1 ? 's' : ''}}</p>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>