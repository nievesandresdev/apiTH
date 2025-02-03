<div style="margin-top:32px; margin-left: 16px; margin-right: 16px;">
    <!-- Título -->
    <h2 style="color: #333; font-family: arial;font-size: 22px;font-weight: 500;line-height: 145.455%">
        Disfrutarás de estas instalaciones y más
    </h2>

    <div style="margin-top:16px">
        <table style="table-layout: fixed; width: 100%" class="table-facilities">
            <!-- Definición de anchos de columna -->
            <colgroup>
                <col style="width: 33.33%">
                <col style="width: 33.33%">
                <col style="width: 33.33%" class="hidden-mobile">
            </colgroup>
            <tr>
                @foreach ($facilities as $key => $item)
                    <td style="padding: 4px; vertical-align: top;">
                        <div style="border-radius: 4px;border: 1px solid #F3F3F3;background: #FFF; height: 100%;">
                            <div style="height: 148px; overflow: hidden;">
                                <img
                                    src="{{$item['url_image']}}"
                                    alt="Catedral de Sevilla"
                                    style="display:block;width:100%;height:100%;object-fit: cover;border-radius:3px 3px 0 0;"
                                >
                            </div>
                            <div style="padding: 8px;">
                                <h2 class="title-card" style="color: #333;font-family: Arial;font-size: 14px;font-weight: 700;line-height: 114.286%;margin:0;">
                                    {!! $item['title'] !!}
                                </h2>
                                <div style="text-align: right;margin-top:8px;margin-bottom:15px;">
                                    <a
                                        style="color:#333;font-family: Arial;font-size: 10.5px;font-weight: 700;line-height: 114.286%;text-decoration: underline;"
                                        href="{{ $item['url_webapp'] }}"
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
