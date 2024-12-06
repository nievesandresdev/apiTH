<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a Thehoster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>

        body {
            font-family: "Roboto", sans-serif;
        }

        .hidden-responsive {
            display: none;
        }


        @media only screen and (max-width: 600px) {
            body {
                background-color: #ffffff !important;
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

            .div-responsive {
                display: block !important;
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
                margin-bottom: 20px; /* Separar la imagen del texto */
            }
        }

        .div-responsive {
            display: none;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto;background-color: #ffff;">
        <div style=" padding-top: 16px; text-align: center; padding-bottom:24px">
            <span style="margin: 0; font-size: 28px;font-style: normal;font-weight: 600;line-height: 110%;">[NOMBRE HOTEL]</span>
        </div>
        <section style="margin: 12px" class="responsive-section">
            <div style="border-radius: 3px 3px 50px 3px; background: #F3F3F3; padding: 40px;">
                <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="text-align: center;
                                   width: 50%;
                                   vertical-align: top;
                                   padding: 0 0 0 10px;" class="hidden-responsive"> <!-- 10px de padding a la izquierda -->
                            <img src="{{ asset('mails/welcome1.png') }}" alt="Welcome Banner" style="width: 227px; height: 240px; max-width: 100%; height: auto;">
                        </td>
                        <!-- Columna de Texto -->
                        <td class="text-content"
                            style="color: #333333;
                                   padding: 0 5px 0 0;
                                   text-align: left;
                                   width: 53%;
                                   vertical-align: top;
                                   padding-top:22px;">
                            <span style="margin: 0; font-size: 26px; font-style: normal; font-weight: 600; line-height: 110%;">Gracias por elegirnos</span>
                            <p style="margin: 10px 0; font-size: 16px; font-style: normal; font-weight: 400; line-height: 110%; margin-top:12px !important;">
                                Hola [nombreHuésped]<br><br> Esperamos que hayas disfrutado de tu estancia con nosotros y haberte brindado la atención de calidad que mereces.<br><br> Deseamos volver a recibirte muy pronto.
                            </p>
                        </td>

                        <!-- Columna de Imagen -->
                        <td style="text-align: center;
                                   width: 50%;
                                   vertical-align: top;
                                   padding: 0 0 0 10px;" class="show-not-responsive"> <!-- 10px de padding a la izquierda -->
                            <img src="{{ asset('mails/welcome1.png') }}" alt="Welcome Banner" style="width: 227px; height: 240px; max-width: 100%; height: auto;">
                        </td>
                    </tr>
                </table>
            </div>
        </section>
        <section style="margin: 12px; padding: 20px; background: #FFFFFF; border-radius: 3px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: center; padding-bottom: 16px;">
                        <img src="{{ asset('mails/icons/reviews/VERYWRONG.svg') }}" alt="Good" style="display: block; margin: 0 auto;">
                        <span style="color: #A0A0A0; text-align: center; font-family: Roboto; font-size: 14px; font-style: normal; font-weight: 500; line-height: 20px; display: block; margin-top: 4px;">Muy mala</span>
                    </td>
                    <td style="text-align: center; padding-bottom: 16px;">
                        <img src="{{ asset('mails/icons/reviews/WRONG.svg') }}" alt="Very Good" style="display: block; margin: 0 auto;">
                        <span style="color: #A0A0A0; text-align: center; font-family: Roboto; font-size: 14px; font-style: normal; font-weight: 500; line-height: 20px; display: block; margin-top: 4px;">Mala</span>
                    </td>
                    <td style="text-align: center; padding-bottom: 16px;">
                        <img src="{{ asset('mails/icons/reviews/NORMAL.svg') }}" alt="Good" style="display: block; margin: 0 auto;">
                        <span style="color: #A0A0A0; text-align: center; font-family: Roboto; font-size: 14px; font-style: normal; font-weight: 500; line-height: 20px; display: block; margin-top: 4px;">Normal</span>
                    </td>
                    <td style="text-align: center; padding-bottom: 16px;">
                        <img src="{{ asset('mails/icons/reviews/GOOD.svg') }}" alt="Very Good" style="display: block; margin: 0 auto;">
                        <span style="color: #A0A0A0; text-align: center; font-family: Roboto; font-size: 14px; font-style: normal; font-weight: 500; line-height: 20px; display: block; margin-top: 4px;">Buena</span>
                    </td>
                    <td style="text-align: center; padding-bottom: 16px;">
                        <img src="{{ asset('mails/icons/reviews/VERYGOOD.svg') }}" alt="Very Good" style="display: block; margin: 0 auto;">
                        <span style="color: #A0A0A0; text-align: center; font-family: Roboto; font-size: 14px; font-style: normal; font-weight: 500; line-height: 20px; display: block; margin-top: 4px;">Muy Buena</span>
                    </td>
                </tr>
            </table>
        </section>







        <!-- Nueva sección añadida aquí -->
        {{-- <div style="background-color: white; padding-top: 32px; padding-bottom: 64px; text-align: center; padding-right: 32px; padding-left: 32px;" class="div-normal">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr style="height: 100%;">
                    <td style="width: 40%; vertical-align: top; padding-right: 32px;">
                        <img src="{{ asset('mails/users/frame.png') }}" alt="Frame Image" style="width: 100%; height: 100%; border-radius: 8px; object-fit: cover;" class="image-frame">
                    </td>
                    <td class="text-content" style="width: 60%; vertical-align: top; text-align: left;">
                        <h2 style="margin: 0;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;">{{ $user->email }}</span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;">{{ $password }}</span></p>
                        <a href="{{ $url }}" style="display: inline-block; padding-top:13px;padding-bottom:13px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 27px; font-weight: 500; width: 100%; box-sizing: border-box; text-align: center;font-size: 16px;font-style: normal;line-height: 110%;">Entrar a Thehoster</a>
                    </td>
                </tr>
            </table>
        </div> --}}



        {{-- <div style="background-color: white; padding: 20px; text-align: center;" class="div-responsive">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr>
                    <td class="text-content order-1" style="width: 60%; vertical-align: top; text-align: left;">
                        <h2 style="margin: 0;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;">{{ $user->email }}</span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;">{{ $password }}</span></p>
                    </td>
                    <td class="text-content order-2" style="width: 60%; vertical-align: top;">
                        <a href="{{ $url }}" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 10px; font-weight: 500; width: 100%; box-sizing: border-box; text-align: center;">Entrar a Thehoster</a>
                    </td>
                    <td class="order-3" style="width: 40%; vertical-align: top; padding-right: 20px;">
                        <img src="{{ asset('mails/users/frame.png') }}" alt="Frame Image" style="width: 100%; height: 187px; border-radius: 8px; max-height: 300px;" class="image-frame">
                    </td>
                </tr>
            </table>
        </div> --}}

        <!-- Footer -->
        {{-- @include('components.mails.footer',['showNotify' => false]) --}}
    </div>
</body>
</html>
