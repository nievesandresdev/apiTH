<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>Nuevo Chat</title>
</head>
<style>
    .footer-content{
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .text-footer{
        max-width: 224px;
    }
    .img-mobile{
        display: none;
    }
    .img-desktop{
        display: block;
    }
    @media only screen and (max-width: 600px) {
        .align-boton{
            text-align: center;
        }
        .img-mobile{
            display: block;
        }
        .img-desktop{
            display: none;
        }
        .footer-content{
            flex-flow: column;
            align-items: start;
        }
        .card1{
            flex-flow: column;
        }
        .text-footer{
            max-width: 100%;
        }
    }
</style>
<body style="margin: 0; padding: 0; background-color: #fff;">
    <div style="background-color: #fff;">
        <p style="font-family: 'Montserrat';margin:0;padding:24px 0; color: #000;text-align: center;font-size: 28px;font-weight: 600;line-height: 110%;">Hotel name</p>
    </div>
    <div style="background: #fff;">
        {{-- card1 --}}
        <div class="card1" style="padding: 40px 24px 48px 24px; background: #f3f3f3;max-width:568px; margin:auto;display:flex">
            <img class="img-mobile" style="width:100%; height: 168px;" src="{{ asset('mails/img-mobile.png') }}" alt="">
            <div>
                <h1 style="font-family: 'Montserrat';margin:0;margin-top:24px;font-size: 24px;font-weight: 600;line-height: 110%;">Tienes un chat pendiente.</h1>
                <p style="font-family: 'Montserrat';margin:0;padding-top:16px; color: #000;font-size: 16px;font-weight: 400;line-height: 150%;">Tienes un chat pendiente en la WebApp que requiere tu atención. Te recomendamos revisarlo lo antes posible.</p>
                <div class="align-boton" style="margin-top:40px;"> 
                    <a style="height: 44px;padding: 12px 96px 12px 97px;border-radius: 6px;background-color: #333;color: #FFF;text-align: center;font-family: 'Montserrat';font-size: 18px;font-weight: 600;line-height: 110%;margin-top:32px ">
                        Ir a la WebApp
                    </a>
                </div>
            </div>
            <img class="img-desktop" style="width:200px; height: 236px;margin-left:24px" src="{{ asset('mails/img-desktop.png') }}" alt="">
        </div>
        {{-- card2 --}}
        <div style="padding: 48px 24px; max-width:479px; margin:auto">
            <h1 style="margin: 0;font-family: 'Montserrat';font-size: 22px;font-weight: 600;line-height: 150%;">Mensaje pendiente</h1>
            <div style="padding-top:24px;">
                <p style="margin: 0;font-size: 16px;font-family: 'Montserrat';font-style: normal;font-weight: 400;line-height: 150%;">Rosa</p>
                <div style="padding: 12px;border-radius: 6px 6px 0px 6px;border: 1px solid #BFBFBF;margin-top:4px">
                    <p style="margin: 0;font-family: 'Montserrat';font-size: 16px;font-weight: 400;line-height: 150%;">No encuentro la secadora de pelo, ¿Me podrían traer una a la habitación porfavor?</p>
                </div>
                <p style="margin: 0;font-family: 'Montserrat';font-size: 14px;font-weight: 400;line-height: 140%;color:#A0A0A0;margin-top:8px;text-align: right;">12 Junio - 14:01</p>
            </div>
        </div>
        {{-- card3 --}}
        <div style="margin-top: 16px;margin-bottom: 64px; padding:0 24px">
            <h1 style="font-family: 'Montserrat';margin:0;font-size: 22px;font-weight: 600;line-height: 150%;text-align: center;">Responde al mensaje</h1>
            <div style="max-width:355px;margin:auto;padding: 32px 32px 40px 32px;background: #F3F3F3; border-radius: 10px;margin-top:24px">
                <p style="font-family: 'Montserrat';margin:0;color: #000;text-align: center;font-size: 14px;font-weight: 500;line-height: 150%; ">Escanea el código QR o dale al botón</p>
                {{-- <div style="width: 170px;height: 170px;margin:auto;border-radius: 7.673px;box-shadow: 0px 3.5px 13.1px 2px rgba(73, 73, 73, 0.30);margin-top:24px;"> --}}
                <img style="width: 170px;height: 170px;margin:auto;display:block" src="{{ asset('mails/qr_url_generica 2.png') }}" alt="">
                {{-- </div> --}}
                <div style="text-align: center; margin-top:40px;">
                    <a style="height: 44px;padding: 12px 76px 12px 78px;border-radius: 6px;background-color: #333;color: #FFF;text-align: center;font-family: 'Montserrat';font-size: 18px;font-weight: 600;line-height: 110%;">
                        Ir a la WebApp
                    </a>
                </div>
            </div>
        </div>

        {{-- footer --}}
        <div style="padding: 24px 32px;background-color:#333">
            <div  class="footer-content">
                <p class="text-footer" style="font-family: 'Montserrat';font-size: 14px;font-weight: 400;line-height: 150%;color:#fff">Av. de Velázquez, 212, Churriana, 29004 Málaga</p>
                <div>
                    <h3 class="footer-w-text" style="margin: 0;font-family: 'Montserrat';font-size: 16px;font-weight: 600;line-height: 150%;color:#fff">Redes sociales</h3>
                    <div style="display: flex;justify-content:center;margin-top:12px">
                        <img style="margin-right: 6px" src="{{ asset('mails/ri_instagram-line.png') }}" alt="">
                        <img style="margin-left: 6px" src="{{ asset('mails/web.png') }}" alt="">
                    </div>
                </div>
            </div>
            <div style="margin:24px 0;display:flex;justify-content:center;gap:16px">
                <p style="margin:0;color: #A0A0A0;font-family: 'Montserrat';font-size: 12px;font-weight: 400;line-height: 150%;">Política de privacidad</p>
                <p style="margin:0;color: #A0A0A0;font-family: 'Montserrat';font-size: 12px;font-weight: 400;line-height: 150%;">Cancelar suscripción</p>
            </div>
        </div>
        <div style="padding: 24px 40px;">
            <p style="text-align: center;font-family: 'Montserrat';margin:0;color: #6E6E6E;font-size: 10px;font-weight: 400;line-height: 154%;">Ha recibido este correo electrónico en relación con su estancia en nuestro alojamiento. Este mensaje tiene como objetivo mejorar su experiencia como cliente y brindarle información relevante sobre nuestros servicios. Para cualquier consulta o comentario adicional sobre su reserva o nuestros servicios, puede responder directamente a este correo o utilizar los datos de contacto proporcionados en nuestro sitio web. Le recordamos que sus datos están siendo tratados de acuerdo con nuestra <span style="font-weight: 600">política de privacidad</span>.</p>
        </div>
    </div>
</body>
</html>