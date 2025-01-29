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
        padding-bottom: 12px;
        border-radius: 6px;
        display: inline-block;
        width: 260px;
        height: 30px;
        text-align: center;
        line-height: 44px;
    }

    @media only screen and (min-width: 601px) {
        .container{
            padding:0;
        }
    }
    @media only screen and (max-width: 600px) {

        .container{
            padding:0 24px;
        }

        /* body {
            background-color: #ffffff !important;
        } */

        .response-button {
            height: 30px; /* Altura reducida */
            line-height: 34px; /* Centrado del texto */
            font-size: 14px; /* Tamaño de fuente más pequeño */
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
            margin-bottom: 20px;
        }
    }

</style>
