<div style="margin-left: 32px; margin-right: 32px; padding-top: 32px;">
    <h2 style="margin:0;color: #333; font-family: Roboto;font-size: 22px;font-weight: 500;line-height: 145.455%">
        {{ __('mail.rewards.howReedem.title') }}
    </h2>
    <p style="margin:0;padding-top:8px;color: #333;font-family: Roboto;font-size: 14px;font-weight: 400;line-height: 175%">
        {{ $reward->description }}
    </p>
    <div style="max-width:260px;margin:0 auto;">
        <a
            href="{{ $reward->url }}"
            style="margin:0;border-radius: 6px;background-color: #333;padding: 12px 0;text-align:center;color:#F3F3F3;font-size: 18px;font-weight: 600;line-height: 110%;font-family:Roboto;margin-top:16px;display:block;text-decoration: none;width:100%"
        >
            {{ __('mail.rewards.howReedem.button') }}
        </a>
    </div>
</div>
