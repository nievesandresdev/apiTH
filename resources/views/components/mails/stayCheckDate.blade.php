<div style=" margin-left: 16px; margin-right: 16px; background-color: #ffffff; padding-top: 24px;">
    <h1 class="title-section" style="margin: 0 auto;color:#333;font-family: arial;font-weight: 600;font-size: 22px;line-height: 150%;">
        {{$title}}
    </h1>
    <div class="dates-container" style="border: 1px solid #F3F3F3;">
        <table style="width: 100%;">
            <tr>
                <td>
                    <p class="checkin-title" style="margin: 0;color:#A0A0A0;font-family: arial;line-height: 110%">Check-in</p>
                    <div class="checkin-description" style="margin:0 10px;">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <h1 class="h1" style="margin:0;color:#858181;font-family: arial;font-weight: 600;">
                                        {{$formatCheckin['dayDate'] ?? '02-05-1993'}}
                                    </h1>
                                </td>
                                <td style="padding-left: 2px;">
                                    <h5 class="h5" style="margin:0;color: #858181;font-family: Arial;font-weight: 400; padding-botton:10px !important;">{{$formatCheckin['weekDay'] ?? 12}}</h5>
                                    <h2 class="h2" style="margin:0;color: #858181;font-family: Arial;font-weight: 400; padding-botton:10px !important;text-transform: uppercase;">{{$formatCheckin['month'] ?? 5}}</h2>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td>
                    {{-- <div style="width:1.713px;background-color:#E9E9E9;height:114.745px;"></div> --}}
                    <div style="width:1.713px;background-color:#E9E9E9;height:114.745px;"></div>
                </td>
                <td>
                    <p class="checkout-title" style="margin: 0;color:#A0A0A0;font-family: arial;line-height: 110%">Check-out</p>
                    <div class="checkout-description" style="margin:0 auto;">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <h1 class="h1" style="margin:0;color:#858181;font-family: arial;font-weight: 600;">
                                        {{$formatCheckout['dayDate'] ?? 10}}
                                    </h1>
                                </td>
                                <td style="padding-left: 2px;">
                                    <h5 class="h5" style="margin:0;color: #858181;font-family: Arial;font-weight: 400; padding-botton:10px !important;">{{$formatCheckout['weekDay'] ?? 12}}</h5>
                                    <h2 class="h2" style="margin:0;color: #858181;font-family: Arial;font-weight: 400; padding-botton:10px !important;text-transform: uppercase;">{{$formatCheckout['month'] ?? 5}}</h2>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <p class="warning-info" style="color: #A0A0A0;font-family: arial;font-size: 12px;font-weight: 500;line-height: 150%;">
        {{ __('mail.stayCheckDate.warning', ['type' => formatTypeLodging($hotel->type)]) }}
        <a style="font-weight: 600;text-decoration:underline;color: #A0A0A0;" href="{{$editUrl}}">{{ __('mail.stayCheckDate.editUrl') }}</a>
    </p>
</div>
