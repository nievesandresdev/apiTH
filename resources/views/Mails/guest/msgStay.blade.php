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

        .review-label {
            white-space: nowrap;
            font-size: 18px;
            color: #A0A0A0;
            font-family: Roboto, sans-serif;
            font-weight: 500;
            margin-top: 4px;
        }

        .response-button {
            text-decoration: none;
            background: #333;
            color: #FFFF;
            font-family: Roboto, sans-serif;
            font-size: 18px;
            padding: 12px 29px;
            border-radius: 6px;
            display: inline-block;
            width: 260px;
            height: 40px;
            text-align: center;
            line-height: 44px;
        }


        @media only screen and (max-width: 600px) {
            body {
                background-color: #ffffff !important;
            }

            .response-button {
                height: 40px; /* Altura reducida */
                line-height: 34px; /* Centrado del texto */
                font-size: 18px; /* Tamaño de fuente más pequeño */
                padding: 10px 25px; /* Espaciado ajustado */
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
        <section style="margin-right: 12px; margin-left: 12px" class="responsive-section">
            <div style="border-radius: 3px 3px 50px 3px; background: #F3F3F3; padding: 40px;">
                <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="text-align: center;
                                   width: 50%;
                                   vertical-align: top;
                                   padding: 0 0 0 10px;" class="hidden-responsive"> <!-- 10px de padding a la izquierda -->
                            <img src="{{ asset('mails/welcome2.png') }}" alt="Welcome Banner" style="width: 227px; height: 240px; max-width: 100%; height: auto;">
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
        <section style="margin: 12px; background: #FFFFFF; border-radius: 3px;">
            <!-- Texto y preguntas -->
            <p style="text-align: left; font-family: Roboto, sans-serif; font-size: 16px; line-height: 24px; margin: 0;">
                ¿Cómo ha sido tu estancia con nosotros?
            </p>

            <!-- Tabla de íconos -->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 16px; text-align: center; padding-left: 40px; padding-right: 50px">
                <tr>
                    <!-- Muy Mala -->
                    <td style="text-align: center; padding: 0 10px;">
                        <a href="#" target="_blank" style="text-decoration: none;">
                            <img src="{{ asset('mails/icons/reviews/VERYWRONG.png') }}" alt="Muy Mala" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                            <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Muy Mala</p>
                        </a>
                    </td>
                    <!-- Mala -->
                    <td style="text-align: center; padding: 0 10px;">
                        <a href="#" target="_blank" style="text-decoration: none;">
                            <img src="{{ asset('mails/icons/reviews/WRONG.png') }}" alt="Mala" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                            <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Mala</p>
                        </a>
                    </td>
                    <!-- Normal -->
                    <td style="text-align: center; padding: 0 10px;">
                        <a href="#" target="_blank" style="text-decoration: none;">
                            <img src="{{ asset('mails/icons/reviews/NORMAL.png') }}" alt="Normal" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                            <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Normal</p>
                        </a>
                    </td>
                    <!-- Buena -->
                    <td style="text-align: center; padding: 0 10px;">
                        <a href="#" target="_blank" style="text-decoration: none;">
                            <img src="{{ asset('mails/icons/reviews/GOOD.png') }}" alt="Buena" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                            <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Buena</p>
                        </a>
                    </td>
                    <!-- Muy Buena -->
                    <td style="text-align: center; padding: 0 10px;">
                        <a href="#" target="_blank" style="text-decoration: none;">
                            <img src="{{ asset('mails/icons/reviews/VERYGOOD.png') }}" alt="Muy Buena" style="width: 32px; height: 32px; display: block; margin: 0 auto;">
                            <p class="review-label" style="color: #A0A0A0; font-family: Roboto, sans-serif; font-size: 14px; font-weight: 500; margin-top: 4px; white-space: nowrap;">Muy Buena</p>
                        </a>
                    </td>
                </tr>
            </table>

            <!-- Botón -->
            <div style="text-align: center; margin-top: 20px;">
                <a href="#" class="response-button" target="_blank">
                    Responder en la WebApp
                </a>
            </div>
        </section>

        <!-- Línea horizontal -->
        <hr style="border: 1px solid #E0E0E0; margin-top: 32px; margin-bottom: 32px;">

        <section style="margin: 32px; background: #FFFFFF;">
            <!-- Contenedor de las cards -->
            <div style="display: flex; gap: 8px; justify-content: flex-start; flex-wrap: nowrap; overflow-x: auto;">
                <!-- Card 1 -->
                <div style="width: 300px; border: 1px solid #E0E0E0; border-radius: 8px; overflow: hidden; background: #FFF; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <img src="https://imagen.research.google/main_gallery_images/a-brain-riding-a-rocketship.jpg" alt="Catedral de Sevilla" style="width: 100%; height: 180px; object-fit: cover;">
                    <div style="padding: 16px;">
                        <h3 style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #333; font-family: Roboto, sans-serif; font-size: 14px; font-style: normal; font-weight: 500; margin: 8px 0 0;">
                            Catedral de Sevilla
                        </h3>
                        <div style="margin-top: 8px; display: flex; align-items: center; color: #333;">
                            <span style="font-size: 16px;">⭐</span>
                            <span style="font-family: Roboto, sans-serif; font-size: 16px; margin-left: 4px;">5.0</span>
                        </div>
                        <a href="#" style="display: inline-block; margin-top: 16px; text-decoration: none; font-family: Roboto, sans-serif; font-size: 14px; color: #007BFF; text-align: left;">Ver en la WebApp</a>
                    </div>
                </div>

                <!-- Card 2 -->
                <div style="width: 300px; border: 1px solid #E0E0E0; border-radius: 8px; overflow: hidden; background: #FFF; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <img src="https://imagen.research.google/main_gallery_images/a-brain-riding-a-rocketship.jpg" alt="La cantina de Pepe" style="width: 100%; height: 180px; object-fit: cover;">
                    <div style="padding: 16px;">
                        <h3 style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #333; font-family: Roboto, sans-serif; font-size: 14px; font-style: normal; font-weight: 500; margin: 8px 0 0;">
                            La cantina de Pepe
                        </h3>
                        <div style="margin-top: 8px; display: flex; align-items: center; color: #333;">
                            <span style="font-size: 16px;">⭐</span>
                            <span style="font-family: Roboto, sans-serif; font-size: 16px; margin-left: 4px;">5.0</span>
                        </div>
                        <a href="#" style="display: inline-block; margin-top: 16px; text-decoration: none; font-family: Roboto, sans-serif; font-size: 14px; color: #007BFF; text-align: left;">Ver en la WebApp</a>
                    </div>
                </div>

                <!-- Card 3 -->
                <div style="width: 300px; border: 1px solid #E0E0E0; border-radius: 8px; overflow: hidden; background: #FFF; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <img src="https://imagen.research.google/main_gallery_images/a-brain-riding-a-rocketship.jpg" alt="Centro comercial Torre" style="width: 100%; height: 180px; object-fit: cover;">
                    <div style="padding: 16px;">
                        <h3 style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #333; font-family: Roboto, sans-serif; font-size: 14px; font-style: normal; font-weight: 500; margin: 8px 0 0;">
                            Centro comercial Torre
                        </h3>
                        <div style="margin-top: 8px; display: flex; align-items: center; color: #333;">
                            <span style="font-size: 16px;">⭐</span>
                            <span style="font-family: Roboto, sans-serif; font-size: 16px; margin-left: 4px;">5.0</span>
                        </div>
                        <a href="#" style="display: inline-block; margin-top: 16px; text-decoration: none; font-family: Roboto, sans-serif; font-size: 14px; color: #007BFF; text-align: left;">Ver en la WebApp</a>
                    </div>
                </div>
            </div>
        </section>





        <!-- Footer -->
        {{-- @include('components.mails.footer',['showNotify' => false]) --}}
    </div>
</body>
</html>
