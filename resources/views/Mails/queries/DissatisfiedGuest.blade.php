<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>
        Huésped disconforme
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        td{
            padding: 0;
        }
        .container-text {
            margin: 50px 0;
        }
        .title {
            font-size: 32px;
            font-weight: 600;
        }
        .container-hero {
            padding: 40px 32px;
        }

        .go-report-button {
            border-radius: 6px;
            background: #FFD453;
            width: 264px;
            height: 44px;
            padding: 13px 54px;
            text-align: center;

            color: #333;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 110%;
            border: none;
            text-decoration: none;
        }
        .container-content {
            padding: 24px 32px 40px 32px;
        }
        .card-wrong .icon{
            width: 64px;
            height: 64px;
            margin-right: 12px;
        }

        .card-wrong .text-data{
            font-size: 14px;
            font-weight: 700;
            line-height: 110%;
        }

        .card-wrong .card-link{
            font-size: 14px;
            font-weight: 700;
        }

        @media only screen and (max-width: 600px) {
            .go-report-button {
                padding: 15px 79px 14px 80px;
                width: 100%;
            }
            .container-hero {
                padding: 32px;
            }
            .container-text {
                margin: 0;
            }
            .title {
                font-size: 24px;
                font-weight: 700;
            }
            .order-2{
                margin-top: 24px;
            }
            body {
                background-color: #ffffff !important;
            }
            .responsive-table, .responsive-table-2 {
                width: 100% !important;
                display: block !important;
            }
            .responsive-table td, .responsive-table-2 td {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
            }
            .responsive-table .text-content, .responsive-table-2 .text-content {
                text-align: left !important;
            }
            .full-width-button {
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }
            .image-frame {
                height: auto !important;
            }
            .responsive-table .order-1 {
                order: 1;
            }
            .responsive-table .order-2 {
                order: 2;
            }
            .responsive-table .order-3 {
                order: 3;
                display: none !important;
            }
            .responsive-table .order-4 {
                order: 3;
                display: block !important;
            }

            .card-wrong .icon{
                width: 48px;
                height: 48px;
            }
            .text-data{
                font-size: 12px;
                font-weight: 500;
            }

            .card-wrong .card-link{
                font-size: 12px;
                font-weight: 500;
            }
        }

        .order-4 {
            display: none !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA; font-family: 'Roboto', sans-serif;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffff;">
        @include('components.mails.headerSimpleLogo')
        <!-- hero section -->
        <!-- hero section -->
        <!-- hero section -->
        <div class="container-hero" style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%);">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family: 'Roboto', sans-serif;">
                <tr>
                    <td class="text-content order-1" style="color: white; text-align: left; width: 50%; vertical-align: top; font-family: 'Roboto', sans-serif;">
                        <div class="container-text">
                            <h1 class="title" style="line-height: 110%; font-family: 'Roboto', sans-serif;margin: 0;">Huésped disconforme</h1>
                            <span style="display: block; margin-top: 12px; font-size: 16px; font-style: normal; font-weight: 500; line-height: 130%; font-family: 'Roboto', sans-serif;">
                                Un huésped ha indicado que está disconforme con su estancia. Te sugerimos contactarle.
                            </span>
                        </div>
                    </td>
                    <td class="order-2" style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="{{ asset('mails/DissatisfiedGuest.png') }}" alt="Chat Image" style="width: 216px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- content section -->
        <!-- content section -->
        <!-- content section -->
        <div class="container-content">
            <div class="card-wrong" style="border-radius: 20px;border: 2px solid #E9E9E9;background: #FFF;padding: 24px;">
                <h2 style="font-size: 20px;font-weight: 500;line-height: 110%;font-family: 'Roboto', sans-serif;margin: 0;">{{$data['guestName']}}</h2>
                <div style="margin-top: 16px;">
                    <table>
                        <tr>
                            <td>
                                <img class="icon" src="{{ asset('mails/icons/reviews/VERYWRONG2.png') }}">
                            </td>
                            <td>
                                <table>
                                    <tr>
                                        <td><img src="{{ asset('mails/icons/1.TH.schedule.png') }}" style="width: 16px; height: 16px;margin-right: 8px;"></td>
                                        <td>
                                            <p class="text-data" style="margin:0; padding:0; font-family: 'Roboto', sans-serif !important;">{{$data['textDate']}}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><img src="{{ asset('mails/icons/1.TH.CLOCK.png') }}" style="width: 16px; height: 16px;margin-right: 8px;margin-top: 2px;"></td>
                                        <td><p class="text-data" style="margin:0; padding:0; font-family: 'Roboto', sans-serif !important;">{{$data['respondedHour']}}</p></td>
                                    </tr>
                                    <tr>
                                        <td><img src="{{ asset('mails/icons/flags/png/'.$data['langAbbr'].'.png') }}" style="width: 16px; height: 16px;margin-right: 8px;margin-top: 2px;"></td>
                                        <td><span class="text-data" style="margin:0; padding:0; font-family: 'Roboto', sans-serif !important;">{{$data['languageResponse']}}</span><span style="font-size: 12px; font-weight: 400; line-height: 110%;text-style: italic; font-family: 'Roboto', sans-serif !important;"> (Idioma original)</span></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <h2 style="font-size: 16px;font-weight: 500;line-height: 130%;font-family: 'Roboto', sans-serif;margin-top: 16px;">{{ $data['question'] }}</h2>
                <p style="font-size: 14px;font-weight: 400;line-height: 150%;font-family: 'Roboto', sans-serif;margin-top: 8px;">
                    {{ $data['comment']}}
                </p>
                <div style="margin-top: 16px;text-align: right;">
                    <a class="card-link" href="{{$data['urlToStay']}}" style="text-decoration: underline;color: #333;line-height: 130%; font-family: 'Roboto', sans-serif !important;">Ver la estancia</a>
                </div>
            </div>

            <div style="margin-top: 32px;text-align: center; font-family: 'Roboto', sans-serif !important;">
                <a href="mailto:{{$data['guestEmail']}}" class="go-report-button" style="font-family: 'Roboto', sans-serif !important; color:#333333 !important;">
                    Responder al huésped
                </a>
            </div>
        </div>


        <!-- Footer -->
        @include('components.mails.footer')
    </div>
</body>
</html>
