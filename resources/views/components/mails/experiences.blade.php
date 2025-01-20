<div class="container-exp" style="max-width: 488px;margin:0 auto;margin-top:32px;padding:0;; margin-left: 16px; margin-right: 16px;">
    <!-- Título -->
    @if($type == 'welcome' || $type == 'postCheckin')

        <h2 style="margin:0; color: #333; font-family: arial;font-size: 22px;font-weight: 500;line-height: 145.455%">
            Podrás vivir experiencias inolvidables
        </h2>
    @endif

    @if($type == 'checkout')
        <h2 style="margin:0; color: #333; font-family: arial;font-size: 22px;font-weight: 500;line-height: 145.455%">
            Te quedan experiencias por vivir
        </h2>
    @endif

    <div class="exp-desktop" style="margin-top:16px">
        <table style="table-layout: fixed;width:100%">
            <!-- Definición de anchos de columna -->
            <colgroup>
                <col class="col-space-exp">
                <col class="col-space-exp">
            </colgroup>
            <tr>
                @foreach ($exp as $item)
                <td>
                    <div style="border-radius: 4px;border: 1px solid #F3F3F3;background: #FFF;padding:1px;">
                        <img
                            class="card-facility"
                            src="{{$item['image_url']}}"
                            alt="{{$item['title']}}"
                            style="display:block;border-radius:3px 3px 0 0;object-fit: cover;width:100%;height:148px"
                        >
                        <div style="padding: 8px;">
                            <h2 style="margin:0;height: 32px;overflow: hidden;color: #333;font-family: Arial;font-size: 14px;font-weight: 500;line-height: 120%;">
                                {{$item['title']}}
                            </h2>


                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: auto;margin-top:4px">
                                <tr>
                                    <td style="width: auto; padding-right: 4px;">
                                        <img src="{{ asset('mails/WA.star.png') }}" alt="Estrella" style="width: 16px; height: 16px;">
                                    </td>
                                    <td style="width: auto;">
                                        <span style="color: #333; font-size: 16px; font-weight: 700;font-family:Arial;"> {{ str_replace(',', '.', $item['num_stars'] ?? '0') }}</span>
                                    </td>
                                </tr>
                            </table>
                            <div style="text-align: right;margin-top:16px;margin-bottom:16px;">
                                <a
                                    href="{{$item['url_webapp']}}"
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

    <div class="exp-mobile" style="margin-top:16px">
        <table style="table-layout: fixed;width:100%">
            @foreach ($exp as $item)
            <tr>
                <td>
                    <div style="border-radius: 4px;border: 1px solid #F3F3F3;background: #FFF;padding:1px;">
                        <img
                            class="card-facility"
                            src="{{$item['image_url']}}"
                            alt="{{$item['title']}}"
                            style="display:block;border-radius:3px 3px 0 0;object-fit: cover;width:100%;height:148px"
                        >
                        <div style="padding: 8px;">
                            <h2 style="margin:0;height: 32px;overflow: hidden;color: #333;font-family: Arial;font-size: 14px;font-weight: 500;line-height: 120%;">
                                {{$item['title']}}
                            </h2>


                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: auto;margin-top:4px">
                                <tr>
                                    <td style="width: auto; padding-right: 4px;">
                                        <img src="{{ asset('mails/WA.star.png') }}" alt="Estrella" style="width: 16px; height: 16px;">
                                    </td>
                                    <td style="width: auto;">
                                        <span style="color: #333; font-size: 16px; font-weight: 700;font-family:Arial;">{{$item['num_stars']}}</span>
                                    </td>
                                </tr>
                            </table>
                            <div style="text-align: right;margin-top:16px;margin-bottom:16px;">
                                <a
                                    href="{{$item['url_webapp']}}"
                                    style="color:#333;font-family: Arial;font-size: 10.5px;font-weight: 700;line-height: 114.286%;text-decoration: underline;"
                                >
                                    Ver en la WebApp
                                </a>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>


