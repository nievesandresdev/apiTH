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
            height: 30px;
            text-align: center;
            line-height: 44px;
        }


        @media only screen and (max-width: 600px) {
            body {
                background-color: #ffffff !important;
            }

            .response-button {
                height: 30px; /* Altura reducida */
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

            /* Mostrar 3 Cards en pantallas grandes */
    .desktop-only {
        display: block;
    }

    /* Mostrar 2 Cards en pantallas pequeñas */
    .mobile-only {
        display: none;
    }

    @media only screen and (max-width: 1024px) {
        .desktop-only {
            display: none !important;
        }
        .mobile-only {
            display: block !important;
        }
    }

    @media only screen and (max-width: 768px) {
        .desktop-only {
            display: none !important;
        }
        .mobile-only {
            display: block !important;
        }
    }

    @media only screen and (max-width: 480px) {
        .desktop-only {
            display: none !important;
        }
        .mobile-only {
            display: block !important;
        }
    }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto;background-color: #ffff;">
        <div style=" padding-top: 16px; text-align: center; padding-bottom:24px">
            <span style="margin: 0; font-size: 28px;font-style: normal;font-weight: 600;line-height: 110%;">[NOMBRE HOTEL]</span>
        </div>

        @include('components.mails.headerBye')

        @include('components.mails.feedback')

        @include('components.mails.places')

        @include('components.mails.stayCheckDate')




        <!-- Footer -->
        {{-- @include('components.mails.footer',['showNotify' => false]) --}}
    </div>
</body>
</html>
