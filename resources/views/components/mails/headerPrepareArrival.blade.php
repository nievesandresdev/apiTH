<section style="" class="responsive-section">
    <div class="hero-section-postcheckin" style="border-radius: 3px 3px 50px 3px; background: #F3F3F3;">
        <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                {{-- mobile image --}}
                <td style="text-align: center;width: 50%;vertical-align: top;padding: 0 0 0 10px;" class="hidden-responsive"> <!-- 10px de padding a la izquierda -->
                    <img src="{{ asset('mails/arrival2.png') }}" alt="arrival1" style="width: 100%; height: 240px; max-width: 100%;object-fit:cover">
                </td>
                <!-- Columna de Texto -->
                <td class="text-content" style="color: #333333;text-align: left;vertical-align: top;padding-right:20px;">
                    <h1 style="margin: 0;color: #333;font-family: arial;font-size: 26px;font-weight: 600;line-height: 110%;">{{ __('mail.prepareArrival.headerTitle', ['guest_name' => $guest_name]) }}</h1>
                    <p style="margin: 0;color: #333;font-family: arial;font-size: 16px;font-weight: 400;line-height: 28px;margin-top:14px">
                        {{ __('mail.prepareArrival.headerBody', ['hotel' => $hotel_name]) }}
                    </p>
                    <a
                        href="{{ $data['urlWebapp'] }}"
                        style="margin:0;border-radius: 6px;background-color: #333;padding: 12px 0;text-align:center;color:#F3F3F3;font-size: 18px;font-weight: 600;line-height: 110%;font-family:Arial;margin-top:16px;display:block;text-decoration: none;width:100%"
                    >
                        {{ __('mail.prepareArrival.button') }}
                    </a>


                </td>

                <!-- Columna de Imagen -->
                <td style="text-align: center;vertical-align: top;width: 212px;padding: 0 0 0 10px;" class="show-not-responsive"> <!-- 10px de padding a la izquierda -->
                    <img src="{{ asset('mails/arrival1.png') }}" alt="arrival2" style="width: 208px; height: 254px; max-width: 100%;">
                </td>
            </tr>
        </table>
    </div>
</section>
<style>
    h1{
        color:red;
    }
</style>
