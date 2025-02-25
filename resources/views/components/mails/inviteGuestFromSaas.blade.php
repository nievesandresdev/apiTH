<section class="hero-section" style="max-width: 568px;background: #F3F3F3;border-radius: 3px 3px 50px 3px;margin:0 auto;">
    <table style="width: 100%">
        <tr>
            <td style="vertical-align: top;">
                <img class="img-mobile" style="width: 100%;height: 168px;padding-bottom:24px;" src="{{ asset('mails/invitar-a-huesped-sass-mobile.png') }}" alt="">
                <div class="container-text">
                    <h1 class="title" style="margin:0;font-weight: 600;line-height: 110%;font-family:Arial;">Únete ahora a la WebApp de {{$hotel->name}}</h1>
                    <p class="description" style="margin:0;color: #333;font-weight: 400;line-height: 28px;margin-top:24px;font-family:Arial;">
                        Únete a la WebApp de {{$hotel->name}} y descubre cómo aprovechar tu viaje al máximo.
                    </p>
                </div>
                <a
                    href="{{$urlWebapp}}"
                    style="margin:0;border-radius: 6px;background-color: #333;padding: 12px 1px;text-align:center;color:#F3F3F3;font-size: 18px;font-weight: 600;line-height: 110%;font-family:Arial;margin-top:16px;display:block;text-decoration: none;"
                >
                    Explorar la WebApp
                </a>
            </td>
            <td class="td-desktop" style="padding-left: 24px;">
                <img class="img-desktop" style="width: 208px;height: 247px;" src="{{ asset('mails/invitar-a-huesped-sass-desktop.png') }}" alt="">
            </td>
        </tr>
    </table>
</section>
