<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

        body {
            font-family: "Roboto", sans-serif;
        }

        .hidden-responsive {
            display: none;
        }

        .review-label {
            white-space: nowrap;
            font-size: 18px;
            color: #A0A0A0;
            font-family: Roboto, sans-serif;
            font-weight: 500;
            margin-top: 4px;
        }

        .response-button {
            text-decoration: none;
            background: #333;
            color: #FFFF;
            font-family: Roboto, sans-serif;
            font-size: 18px;
            padding-bottom: 12px;
            border-radius: 6px;
            display: inline-block;
            width: 260px;
            height: 30px;
            text-align: center;
            line-height: 44px;
        }

        @media only screen and (min-width: 601px) {
            .container{
                padding:0;
            }
        }
        @media only screen and (max-width: 600px) {

            .container{
                padding:0 24px;
            }

            /* body {
                background-color: #ffffff !important;
            } */

            .response-button {
                height: 30px; /* Altura reducida */
                line-height: 34px; /* Centrado del texto */
                font-size: 14px; /* Tamaño de fuente más pequeño */
                padding: 10px 25px; /* Espaciado ajustado */
            }
            .responsive-table, .responsive-table-2 {
                width: 100% !important;
                display: block !important;
            }
            .responsive-table td, .responsive-table-2 td {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                padding: 10px 0 !important;
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

            .hidden-responsive {
                display: block;
            }

            .show-not-responsive {
                display: none !important;
            }

            .responsive-section .show-not-responsive {
                display: none !important;
            }


            .div-normal {
                display: none;
            }

            .responsive-section {
                margin: 0 !important; /* Elimina el margen en pantallas pequeñas */
            }

            .responsive-section table {
                display: block;
            }
            .responsive-section td {
                display: block;
                width: 100%;
                text-align: center;
            }
            .responsive-section td img {
                margin-bottom: 20px;
            }
        }

    </style>
    @include('components.mails.inviteGuestFromSaasStyles')
    @include('components.mails.headerPostCheckinStyles')

    @include('components.mails.rewards.redemSectionStyles')
</head>
<body style="margin: 0; padding: 0px; background-color:#FAFAFA;">
    <div style="max-width: 568px; margin: 0 auto; padding: 0; background-color: #ffff;">
        <div class="content-container" style="max-width: 568px; margin: 0 auto; padding: 0 12px; background-color: #ffff;">
            <div style="padding-top: 16px; text-align: center; padding-bottom:24px">
                <span style="margin: 0; font-size: 28px; font-style: normal; font-weight: 600; line-height: 110%; color: #333333;">
                    {{ $hotel->name }}
                </span>
            </div>
            @include('components.mails.rewards.headerRewards',['rewardStay' => $rewardStay])

            {{-- @include('components.mails.rewards.redemSection',['rewardStay' => $rewardStay]) --}}
        </div>
        <div class="content-container" style="max-width: 568px; background-color: #ffff;">
            @include('components.mails.rewards.redemSection',['rewardStay' => $rewardStay])
        </div>

        <div class="container" style="max-width: 568px; margin: 0 auto;  padding: 0 12px; background-color: #ffffff;">
            @include('components.mails.rewards.howReedem',['reward' => $rewardStay->reward,'hotel' => $hotel])
            <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>

            @if($hotel->chatSettings?->show_guest)
                @include('components.mails.chatLink',['webappChatLink' => $data['webappChatLink']])
                <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
            @endif

            @include('components.mails.qrHotel',['urlQr' => $data['urlQr']])
        </div>
    </div>

    <!-- Footer -->

        @include('components.mails.footerRed')

</body>


</html>
