<div style="max-width: 488px;margin:0 auto;padding:0 24px;">
    <!-- Título -->
    <h2 style="color: #333; font-family: arial;font-size: 22px;font-weight: 500;line-height: 145.455%">
        Disfrutarás de estas instalaciones y más
    </h2>

    <div style="margin-top:16px">
        <table style="table-layout: fixed;" class="table-facilities">
            <!-- Definición de anchos de columna -->
            <colgroup>
                <col class="col-space">
                <col class="col-space">
                <col class="col-space">
            </colgroup>
            <tr>
                @foreach ($crosselling['facilities'] as $key => $item)
                    <td class="{{ $key == 2 ? 'col-3-desktop' : '' }}">
                        <div style="border-radius: 4px;border: 1px solid #F3F3F3;background: #FFF;padding:1px;">
                            <img 
                                class="card-facility"
                                src="{{$item['url_image']}}" 
                                alt="Catedral de Sevilla" 
                                style="display:block;border-radius:3px 3px 0 0"
                            >
                            <div style="padding: 8px;">
                                <h2 class="title-card">
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

