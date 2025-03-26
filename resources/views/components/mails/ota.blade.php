<div style="margin-left: 16px; margin-right: 16px; margin-top: 32px;">
    <h2 style="margin: 0; color: #333; font-family: Arial; font-size: 22px; font-weight: 500; line-height: 145.455%;">
        {{ __('mail.otas.title') }}
    </h2>
    <p style="margin: 0; padding-top: 8px; color: #333; font-family: Arial; font-size: 16px; font-weight: 400; line-height: 175%;">
        {{ __('mail.otas.subtitle') }}
    </p>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 24px; text-align: center;">
    <?php foreach ($otas as $ota): ?>
        @if($ota['url'] != null)
            <tr>
                <td align="center" style="padding: 12px 16px;">
                    <a href="{{ $ota['url'] }}" target="_blank" style="text-decoration: none; display: block; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff; border: 1px solid #dddddd; border-radius: 8px; overflow: hidden;">
                            <tr>
                                <!-- Logo Section with Diagonal Background -->
                                <td style="padding: 0; background: linear-gradient(105deg, #F3F3F3 75%, transparent 40%); width: 50px; text-align: center;">
                                    <img src="<?= asset('mails/icons/services/' . $ota['name'] . '.svg'); ?>" alt=" {{ $ota['name'] }}" style="width: 24px; height: 24px; display: block; margin: 9px" />
                                </td>
                                <!-- Text Section -->
                                <td style="padding: 12px; font-family: Arial, sans-serif; font-size: 14px; font-weight: 400; color: #333333; text-align: left;">
                                   {{ __('mail.otas.button', ['ota' => $ota['name']]) }}
                                </td>
                            </tr>
                        </table>
                    </a>
                </td>
            </tr>
        @endif
    <?php endforeach; ?>
</table>



