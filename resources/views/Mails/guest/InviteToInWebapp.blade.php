<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Invitar Huesped a entrar en la webapp</title>
    <style>

        @media only screen and (min-width: 601px) {
            .hero-section {
                padding: 40px;
            }
            .hero-section .container-text{
                max-width: 256px;
            }
            .hero-section .img-mobile{
                display: none;
            }

            /* .stay-checkdates{
                margin-top: 48px;
            } */

            .facilities{
                margin-top: 48px;
            }
        }
        @media only screen and (max-width: 600px) {
            .hero-section {
                padding: 40px 24px;
            }
            .hero-section .description{
                padding-bottom: 16px;
            }
            .hero-section .td-desktop{
                display: none;
            }
        }
    </style>
    @include('components.mails.stayCheckDateStyles')
    @include('components.mails.facilitiesAndPlacesStyles')
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    {{-- name hotel --}}
    <section style="width: 100%; max-width: 600px; margin: 0 auto;background-color: #ffff;">
        <div style="padding: 24px 0; text-align: center">
            <span style="margin: 0; font-size: 28px;font-style: normal;font-weight: 600;line-height: 110%;font-family:Arial;">{{$hotel->name}}</span>
        </div>
    </section>

    {{-- hero section --}}
    <section class="hero-section" style="max-width: 568px;background: #F3F3F3;border-radius: 3px 3px 50px 3px;margin:0 auto;">
        <table style="width: 100%">
            <tr>
                <td style="vertical-align: top;">
                    <img class="img-mobile" style="width: 100%;height: 168px;padding-bottom:24px;" src="{{ asset('mails/invitar-a-huesped-sass-mobile.png') }}" alt="">
                    <div class="container-text">
                        <h1 style="margin:0;font-size: 26px;font-weight: 600;line-height: 110%;font-family:Arial;">Únete ahora a la WebApp de {{$hotel->name}}</h1>
                        <p class="description" style="margin:0;color: #333;font-size: 16px;font-weight: 400;line-height: 28px;margin-top:24px;font-family:Arial;">
                            Únete a la WebApp de {{$hotel->name}} y descubre cómo aprovechar tu viaje al máximo.
                        </p>
                    </div>
                    <div style="margin:0;border-radius: 6px;background-color: #333;padding: 12px 51px;text-align:center;color:#F3F3F3;font-size: 18px;font-weight: 600;line-height: 110%;font-family:Arial;margin-top:16px;">
                        Explorar la WebApp
                    </div>
                </td>
                <td class="td-desktop" style="padding-left: 24px;">
                    <img class="img-desktop" style="width: 208px;height: 247px;" src="{{ asset('mails/invitar-a-huesped-sass-desktop.png') }}" alt="">
                </td>
            </tr>
        </table>
    </section>

    {{-- <section class="stay-checkdates">
        @include('components.mails.stayCheckDate')
    </section> --}}

    <section class="facilities">
        @include('components.mails.places')
    </section>

    <section class="facilities">
        @include('components.mails.facilities')
    </section>
    {{-- este es el ancho del aproximado gmail en movil --}}
    {{-- <div style="max-width: 326px;background-color:#9f1a1a;height: 140px;"></div> --}}

    <div style="max-width: 474px;margin: 48px auto;background-color:#E9E9E9;height: 1px;"></div>
</body>
</html>
