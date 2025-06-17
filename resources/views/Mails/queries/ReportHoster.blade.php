<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Seguimiento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Roboto", sans-serif;
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
        .container-summary {
            padding: 24px 32px 40px 32px;
        }
        .summary-card {
            padding: 20px;
        }
        .go-report-button {
            text-decoration: none;
            border-radius: 6px;
            background: #FFD453;
            width: 264px;
            height: 44px;
            padding: 13px 54px;
            text-align: center;

            color: #333333 !important;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 110%;
            border: none;
        }
        @media only screen and (max-width: 600px) {
            .go-report-button {
                padding: 15px 79px 14px 80px;
                width: 100%;
            }
            .summary-card {
                padding: 8px 12px;
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
                            <h1 class="title" style="line-height: 110%; font-family: 'Roboto', sans-serif;margin: 0;">Informe de Seguimiento</h1>
                            <span style="display: block; margin-top: 12px; font-size: 16px; font-style: normal; font-weight: 500; line-height: 130%; font-family: 'Roboto', sans-serif;">
                                Aquí te mostramos cómo se sintieron tus huéspedes en el período de {{ $stats['from'] }} a {{ $stats['to'] }}
                            </span>
                        </div>
                    </td>
                    <td class="order-2" style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="{{ asset('mails/report-feedback.png') }}" alt="Chat Image" style="width: 216px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>
        <!-- summary section -->
        <!-- summary section -->
        <!-- summary section -->
        <div class="container-summary">
            {{-- in stay --}}
            @include('components.mails.queries.reportCard', ['period' => 'STAY', 'stats' => $stats['in_stay']])

            {{-- post stay --}}
            @include('components.mails.queries.reportCard', ['period' => 'POST-STAY', 'stats' => $stats['post_stay']])

            {{-- warning section --}}
            @if($stats['in_stay']['total'] == 0 || $stats['post_stay']['total'] == 0)
            <div style="border-radius: 16px;border: 1px solid #333;background: #FFF2CC;padding: 16px;margin-top: 32px;">
                <table>
                    <tr>
                        <td>
                            <img src="{{ asset('mails/icons/1.TH.INFO.png') }}" alt="INFO icon" style="width: 24px; height: 24px;margin:0;margin-right: 4px;">
                        </td>
                        <td>
                            <h4 style="margin: 0; font-size: 16px; font-weight: 500; line-height: 110%; font-family: 'Roboto', sans-serif;">¿Pocas respuestas de Seguimiento?</h4>
                        </td>
                    </tr>
                </table>
                <ul style="margin:0;padding:0;margin-top: 8px;padding-left: 24px;">
                    <li style="margin-top: 8px;">
                        <p style="margin: 0; font-size: 12px; font-weight: 500; line-height: 100%; font-family: 'Roboto', sans-serif;">
                            ¿Estás enviando el enlace de la WebApp a tus huéspedes con los mensajes de confirmación de reserva?
                        </p>
                        <a href="{{$links['urlComunications']}}" target="_blank" style="display: block;margin-top: 4px;font-size: 12px;font-weight: 500;line-height: 140%;font-family: 'Roboto', sans-serif;text-decoration: underline;color: #333333;">
                            Mira como hacerlo aquí
                        </a>
                    </li>
                    <li style="margin-top: 8px;">
                        <p style="margin: 0; font-size: 12px; font-weight: 500; line-height: 100%; font-family: 'Roboto', sans-serif;">
                            ¿Estás utilizando las diferentes formas de promocionar la WebApp que te recomendamos?
                        </p>
                        <a href="{{$links['urlPromotions']}}" target="_blank" style="display: block;margin-top: 4px;font-size: 12px;font-weight: 500;line-height: 140%;font-family: 'Roboto', sans-serif;text-decoration: underline;color: #333333;">
                            Mira nuestras recomendaciones aquí
                        </a>
                    </li>
                    <li style="margin-top: 8px;">
                        <p style="margin: 0; font-size: 12px; font-weight: 500; line-height: 100%; font-family: 'Roboto', sans-serif;">
                            ¿Les comentas a tus huéspedes sobre la WebApp cuando hablas con ellos?
                        </p>
                    </li>
                </ul>
            </div>
            @endif
            {{-- <div style="margin-top: 32px;text-align: center;">
                <a href="{{$links['urlToReport']}}" target="_blank" class="go-report-button">
                    Ver informe completo
                </a>
            </div> --}}
            <div style="max-width:260px;margin:0 auto;margin-top: 32px;">
                <a
                    href="{{$links['urlToReport']}}"
                    style="margin:0;border-radius: 6px;background-color: #FFD453;padding: 12px 0;text-align:center;color:#333333 !important;font-size: 14px;font-weight: 600;line-height: 110%;font-family:'Roboto', sans-serif;margin-top:16px;display:block;text-decoration: none;width:100%"
                >
                    Ver informe completo
                </a>
            </div>
        </div>
        <!-- Footer -->
        @include('components.mails.footer')
    </div>
</body>
</html>
