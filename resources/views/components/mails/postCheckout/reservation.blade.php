<div style="margin-left: 16px; margin-right: 16px; background-color: #ffffff;">
    <h2 style="margin:0;color: #333; font-family: arial;font-size: 22px;font-weight: 500;line-height: 145.455%">
        {{ __('mail.reservation.title') }}
    </h2>
    <p style="margin:0;padding-top:8px;color: #333;font-family: Arial;font-size: 16px;font-weight: 400;line-height: 175%">
        {{ __('mail.reservation.subtitle') }}
    </p>
    <div style="max-width:260px;margin:0 auto;">
        <a
            href="{{$webappChatLink}}"
            style="margin:0;border-radius: 6px;background-color: #333;padding: 12px 0;text-align:center;color:#F3F3F3;font-size: 18px;font-weight: 600;line-height: 110%;font-family:Arial;margin-top:16px;display:block;text-decoration: none;width:100%"
        >
        {{ __('mail.reservation.button') }}
        </a>
    </div>
</div>
