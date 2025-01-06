<div style="max-width: 488px;margin:0 auto;">
    <!-- Título -->
    <h2 style="margin:0; color: #333; font-family: arial;font-size: 22px;font-weight: 500;line-height: 145.455%">
        Te esperan estos Destinos y muchos más
    </h2>

    <div style="margin-top:16px">
        <table style="table-layout: fixed;" class="table-facilities">
            <!-- Definición de anchos de columna -->
            <colgroup>
                <col class="col-space">
                <col class="col-space">
                <col class="col-space hidden-mobile">
            </colgroup>
            <tr>
                @foreach ($places as $key => $item)
                
                <td class="{{ $key == 2 ? 'col-3-desktop' : '' }}">
                    <div style="border-radius: 4px;border: 1px solid #F3F3F3;background: #FFF;padding:1px;">
                        <img 
                            src="{{$item['image']}}" 
                            alt="Catedral de Sevilla" 
                            style="display:block;border-radius:3px 3px 0 0;object-fit: cover;width:100%;height:148px"
                        >
                        <div style="padding: 8px;">
                            <h2 class="title-card">
                                {{$item['title']}}
                            </h2>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: auto;">
                                <tr>
                                    <td style="width: auto; padding-right: 4px;">
                                        <img src="{{ asset('mails/WA.star.png') }}" alt="Estrella" style="width: 16px; height: 16px;">
                                    </td>
                                    <td style="width: auto;">
                                        <span style="color: #333; font-size: 16px; font-weight: 700;font-family:Arial;">{{ $item['num_stars'] }}</span>
                                    </td>
                                </tr>
                            </table>
                            <div style="text-align: right;margin-top:24px;margin-bottom:16px;">
                                <a 
                                    href="{{ $item['url_webapp'] }}"
                                    style="color:#333;font-family: Arial;font-size: 10.5px;font-weight: 700;line-height: 114.286%;text-decoration: underline;"
                                >
                                    Ver en la WebApp
                                </a>
                            </div>
                        </div>
                    </div>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
</div>

