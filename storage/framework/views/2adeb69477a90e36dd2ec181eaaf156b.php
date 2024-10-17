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

            .div-normal {
                display: none;
            }

            .div-responsive {
                display: block !important;
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
            <img src="<?php echo e(asset('mails/users/logo.png')); ?>" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding-top: 40px; padding-bottom:40px;padding-right:32px;padding-left:32px">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content" style="color: white; padding-right: 32px; text-align: left; width: 50%; vertical-align: top; padding-top:22px">
                        <span style="margin: 0; font-size: 32px;font-style: normal;font-weight: 600;line-height: 110%;">¡Bienvenido a TheHoster!</span>
                        <p style="margin: 10px 0; font-size: 16px;font-style: normal;font-weight: 500;line-height: 110%;margin-top:12px !important"><?php echo e($userAuth->name); ?> te ha creado un usuario en la plataforma para que administres el Hotel</p>
                        <p style="margin: 10px 0; font-size: 16px;font-style: normal;font-weight: 700;line-height: 110%;margin-top:24px !important">¡Gracias por elegirnos!</p>
                    </td>
                    <td style="text-align: center; width: 50%; vertical-align: top;">
                        <img src="<?php echo e(asset('mails/users/banner.png')); ?>" alt="Welcome Banner" style="width: 227px; height: 240px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Nueva sección añadida aquí -->
        <div style="background-color: white; padding-top: 32px; padding-bottom: 64px; text-align: center; padding-right: 32px; padding-left: 32px;" class="div-normal">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr style="height: 100%;">
                    <td style="width: 40%; vertical-align: top; padding-right: 32px;">
                        <img src="<?php echo e(asset('mails/users/frame.png')); ?>" alt="Frame Image" style="width: 100%; height: 100%; border-radius: 8px; object-fit: cover;" class="image-frame">
                    </td>
                    <td class="text-content" style="width: 60%; vertical-align: top; text-align: left;">
                        <h2 style="margin: 0;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;"><?php echo e($user->email); ?></span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;"><?php echo e($password); ?></span></p>
                        <a href="<?php echo e($url); ?>" style="display: inline-block; padding-top:13px;padding-bottom:13px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 27px; font-weight: 500; width: 100%; box-sizing: border-box; text-align: center;font-size: 16px;font-style: normal;line-height: 110%;">Entrar a Thehoster</a>
                    </td>
                </tr>
            </table>
        </div>



        <div style="background-color: white; padding: 20px; text-align: center;" class="div-responsive">
            <table class="responsive-table-2" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed;">
                <tr>
                    <td class="text-content order-1" style="width: 60%; vertical-align: top; text-align: left;">
                        <h2 style="margin: 0;">Datos de acceso</h2>
                        <p style="margin: 10px 0; font-weight: 400;">Estas son tus credenciales de acceso a la plataforma.</p>
                        <p style="margin: 10px 0;"><strong>Usuario:</strong> <span style="font-weight: 400;"><?php echo e($user->email); ?></span></p>
                        <p style="margin: 10px 0;"><strong>Contraseña:</strong> <span style="font-weight: 400;"><?php echo e($password); ?></span></p>
                    </td>
                    <td class="text-content order-2" style="width: 60%; vertical-align: top;">
                        <a href="<?php echo e($url); ?>" style="display: inline-block; padding: 10px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; margin-top: 10px; font-weight: 500; width: 100%; box-sizing: border-box; text-align: center;">Entrar a Thehoster</a>
                    </td>
                    <td class="order-3" style="width: 40%; vertical-align: top; padding-right: 20px;">
                        <img src="<?php echo e(asset('mails/users/frame.png')); ?>" alt="Frame Image" style="width: 100%; height: 187px; border-radius: 8px; max-height: 300px;" class="image-frame">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <?php echo $__env->make('components.mails.footer',['showNotify' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\api-hoster\resources\views/Mails/users/welcome.blade.php ENDPATH**/ ?>