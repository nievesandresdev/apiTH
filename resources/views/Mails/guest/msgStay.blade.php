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
    @include('components.mails.stayCheckDateStyles')
    @include('components.mails.facilitiesAndPlacesStyles')
    @include('components.mails.experiencesStyles')
    @include('components.mails.inviteGuestFromSaasStyles')
    @include('components.mails.headerPostCheckinStyles')
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    <div style="background-color: #ffffff;max-width: 568px; margin: 0 auto;">
        <div class="content-container" style="max-width: 568px; margin: 0 auto; padding: 0 12px; background-color: #ffff;">
            <div style=" padding-top: 16px; text-align: center; padding-bottom:24px">
                <span style="margin: 0; font-size: 28px;font-style: normal;font-weight: 600;line-height: 110%;">{{ $hotel->name }}</span>
            </div>
            @if($type == 'welcome')
                @include('components.mails.headerWelcome',['guest_name' => $guest->name,'hotel_name' => $hotel->name,'after' => $after])
            @endif
            @if($type == 'postCheckin')
                @include('components.mails.headerPostCheckin')
            @endif
            @if($type == 'checkout')
                @include('components.mails.headerBye',['guest_name' => $guest->name])
            @endif

            @if($type == 'inviteGuestFromSaas')
                @include('components.mails.inviteGuestFromSaas',['urlWebapp' => $data['urlWebapp']])
            @endif
        </div>
        {{-- <p>IDIOMA ACTUAL: {{ app()->getLocale() }}</p> --}}

        <div class="container" style="max-width: 568px; margin: 0 auto;  padding: 0 12px; background-color: #ffffff;">

            @if($type == 'welcome' && isset($data['checkData']['title']))
                @include('components.mails.stayCheckDate',[
                    'title' => $data['checkData']['title'],
                    'formatCheckin' => $data['checkData']['formatCheckin'],
                    'formatCheckout' => $data['checkData']['formatCheckout'],
                    'editUrl' => $data['checkData']['editStayUrl']
                ])
            @endif

            @if($hotel->show_checkin_stay && ($type != 'inviteGuestFromSaas' && $beforeCheckin))
                <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                @include('components.mails.makeYourCheckLink', ['urlCheckin' => $data['urlCheckin']])
            @endif

            {{-- @if(($type == 'welcome' || $type == 'checkout') && $data['queryData'] && $data['queryData']['showQuerySection']) --}}
            @if((($type == 'welcome' && !$beforeCheckin) || $type == 'checkout' || $type == 'postCheckin') && $data['queryData'] && !$data['test'])
                @if($data['queryData']['currentPeriod'] !== 'in-stay')
                    <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                @endif
                @include('components.mails.feedback',[
                    'currentPeriod' => $data['queryData']['currentPeriod'],
                    'webappLinkInbox' => $data['queryData']['webappLinkInbox'],
                    'webappLinkInboxGoodFeel' => $data['queryData']['webappLinkInboxGoodFeel'],
                    'after' => $after
                ])
            @endif

            @if($data['test'])
                <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                @include('components.mails.needArrivalHotel',['webappLinkInbox' => $data['queryData']['webappLinkInbox']])
            @endif


            @if(count($data['places']) > 0 && $hotel->show_places)
                <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                @include('components.mails.places',['places' => $data['places'] , 'type' => $type])
            @endif

            {{-- @if(count($data['experiences']) > 0 && $hotel->show_experiences)
                @include('components.mails.experiences', ['exp' => $data['experiences'], 'type' => $type])
            @endif --}}

            @if($type == 'welcome' || $type == 'postCheckin' || $type == 'inviteGuestFromSaas')
                @if(count($data['facilities']) > 0 && $hotel->show_facilities)
                    <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                    @include('components.mails.facilities', ['facilities' => $data['facilities']])
                @endif
            @endif

            @if($type == 'welcome' || $type == 'postCheckin' || $type == 'inviteGuestFromSaas')
                @if($hotel->chatSettings?->show_guest)
                    <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                    @include('components.mails.chatLink',['webappChatLink' => $data['webappChatLink']])
                @endif
                <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                @include('components.mails.qrHotel',['urlQr' => $data['urlQr']])
            @endif
            @if($type == 'checkout')
                <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
                @include('components.mails.qrHotel',['urlQr' => $data['urlQr']])
            @endif



        </div>
    </div>

    <!-- Footer -->
    @include('components.mails.footerRed')
</body>
</html>
