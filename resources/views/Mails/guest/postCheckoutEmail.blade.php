<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.mails.generalStyles')
    @include('components.mails.experiencesStyles')
    @include('components.mails.inviteGuestFromSaasStyles')
    @include('components.mails.facilitiesAndPlacesStyles')
    @include('components.mails.headerPostCheckinStyles')
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    <div style="max-width: 568px; margin: 0 auto">
        <div style=" padding-top: 16px; text-align: center; padding-bottom:24px">
            <span style="margin: 0; font-size: 28px;font-style: normal;font-weight: 600;line-height: 110%;">{{ $hotel->name }}</span>
        </div>
        @include('components.mails.postCheckout.headerPostCheckout',['guest_name' => $guest->name,'hotel_name' => $hotel->name,'after' => $after])
    </div>

    <div class="container" style="max-width: 488px; margin-top: 24px; background-color: #ffff;">
        @if($data['queryData'] && !$data['queryData']['answered'] )
            @include('components.mails.feedback',[
                'currentPeriod' => $data['queryData']['currentPeriod'],
                'webappLinkInbox' => $data['queryData']['webappLinkInbox'],
                'webappLinkInboxGoodFeel' => $data['queryData']['webappLinkInboxGoodFeel'],
                'after' => $after
            ])
        @endif

        @if($data['queryData'] && $data['queryData']['answered'])
            @include('components.mails.ota',['otas' => $data['otas'] ])
        @endif

        <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
        @include('components.mails.postCheckout.reservation',['webappChatLink' => $data['reservationURl']])


        <div style="max-width: 474px;margin: 32px auto;background-color:#E9E9E9;height: 1px;"></div>
        @if(count($data['places']) > 0 && $hotel->show_places)
            @include('components.mails.places',['places' => $data['places'] , 'type' => $type])
        @endif

        @include('components.mails.qrHotel',['urlQr' => $data['urlQr']])

    </div>

    <!-- Footer -->
    @include('components.mails.footerRed')
</body>
</html>
