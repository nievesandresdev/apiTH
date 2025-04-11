<div style="padding-top: 32px; background-color: #ffffff; max-width: 568px; margin: 0 auto;">
    <div style="background-color: #333;padding:24px;">
        <div style="max-width: 484px;margin: 0 auto;">
            <p style="color: #FAFAFA;font-family: Arial;font-size: 10px;font-weight: 400;line-height: 140%;margin:0">
                {{ __('mail.footer.body1') }} <span style="font-weight: 600">{{ $hotel->name }}</span> {{ __('mail.footer.body2') }}
            </p>
            <p style="color: #FAFAFA;font-family: Arial;font-size: 10px;font-weight: 400;line-height: 140%;margin:0">
                {{ __('mail.footer.body3') }}
                <a href="{{$data['urlPrivacy']}}" style="font-weight: 600;text-decoration:underline;color: #FAFAFA;">{{ __('mail.footer.body4') }}</a>
            </p>
            <p style="color: #FAFAFA;font-family: Arial;font-size: 10px;font-weight: 400;line-height: 140%;margin:0">
                {{ __('mail.footer.body5') }} <a href="{{$data['urlFooterEmail']}}" style="font-weight: 600;text-decoration:underline;color: #FAFAFA;">{{ __('mail.footer.body6') }}</a>
            </p>
            <p style="color: #FAFAFA;font-family: Arial;font-size: 10px;font-weight: 400;line-height: 140%;margin:0">
                {{ __('mail.footer.body7') }}
            </p>
        </div>
    </div>
</div>
