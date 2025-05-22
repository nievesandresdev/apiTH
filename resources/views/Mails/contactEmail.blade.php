<html>
<head>
</head>
<body style="margin: 0;padding: 0;font-family: Arial, sans-serif; background-color: #fafafa;">
    <div style="padding: 32px 24px;max-width: 600px;margin: 0 auto;">
        <img src="{{ asset('mails/users/logo.png') }}" alt="Welcome Banner" style="width: 150px; height: 32px;">
        <div style="margin-top: 40px;">
            <h1 style="margin:0; padding:0; font-size: 16px; font-weight: 500; line-height: 140%;">Tienes un nuevo mensaje</h1>
            <p style="margin:0; padding:0; font-size: 12px; font-weight: 400; line-height: 140%;margin-top: 8px;">Uno de tus huéspedes te ha enviado un mensaje desde la WebApp</p>
            <div style="margin-top: 22px; padding: 16px; border-radius: 10px;border: 0.623px solid #bfbfbf; width: 552px;background-color: #fff;">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <h1 style="margin:0; padding:0; font-size: 16px; font-weight: 500; line-height: 140%;margin-bottom: 4px;">{{$data['guestName']}}</h1>
                            <a href="mailto:{{$data['guestEmail']}}" style="color: #039;font-size: 14px;font-weight: 400;line-height: 140%;text-decoration-line: underline;">{{$data['guestEmail']}}</a>
                        </td>
                        <td>
                            <div style="text-align: right;">
                                <img src="{{ asset('mails/icons/1.TH.schedule.png') }}" alt="User" style="width: 9.966px;height: 9.966px;display: inline; margin-right: 4px;">
                                <p style="margin:0; padding:0; font-size: 10px; font-weight: 400; line-height: 90%;display: inline;">Estancia: {{$data['stayCheckin']}} al {{$data['stayCheckout']}}</p>
                            </div>
                            <table style="margin-left: auto;">
                                <tr>
                                    <td><p style="margin:0; padding:0; font-size: 10px; font-weight: 400; line-height: 100%;display: inline;margin-right: 4px;">Idioma original:</p></td>
                                    <td><img src="{{ asset('mails/icons/flags/png/'.$data['guestLanguageAbbr'].'.png') }}" alt="User" style="width: 14px;height: 14px;display: inline; margin-right: 4px;margin-bottom: -4px;"></td>
                                    <td><p style="margin:0; padding:0; font-size: 10px; font-weight: 400; line-height: 100%;display: inline;">{{$data['guestLanguageName']}}</p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 16px;">
                    <p style="margin:0; padding:0; font-size: 14px; font-weight: 400; line-height: 150%;">{{$data['message']}}</p>
                </div>
            </div>
        </div>
    </div>
    <div style="max-width: 600px;margin: 0 auto;margin-top: 210px;background: #FFF;box-shadow: 0px -4px 4px 0px rgba(0, 0, 0, 0.25);width: 100%;height: 128px; display:block;padding: 54px 0;box-sizing: border-box;">
        <img src="{{ asset('mails/users/logo.png') }}" alt="Welcome Banner" style="width: 86px; height: 18px;display:block;margin:auto;">
    </div>

</body>
</html>
