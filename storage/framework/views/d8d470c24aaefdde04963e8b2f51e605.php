<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            .responsive-table .order-1 {
                order: 1;
            }
            .responsive-table .order-2 {
                order: 2;
            }
            .responsive-table .order-3 {
                order: 3;
                display: none !important;
            }
            .responsive-table .order-4 {
                order: 3;
                display: block !important;
            }
        }

        .order-4 {
            display: none !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #FAFAFA;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto;background-color: white;">
        <div style="background-color: white; padding: 20px; text-align: center;">
            <img src="<?php echo e(asset('mails/users/logo.png')); ?>" alt="Thehoster Logo" style="display: block; margin: 0 auto;">
        </div>
        <div style="border-radius: 0px 72px 0px 0px; background: linear-gradient(90deg, #0B6357 -17.99%, #34A98F 118.23%); padding-top: 40px; padding-bottom:10px;padding-right:32px;padding-left:32px">
            <table class="responsive-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="text-content order-1" style="color: white; padding-right: 40px; text-align: left; width: 70%; vertical-align: top;">
                        <?php if($type == 'pending'): ?>
                            <span style="font-size: 32px; font-style: normal; font-weight: 600; line-height: 110%; margin: 0;">¡Feedback pendiente de respuesta!</span>
                            <span style="display: block; margin: 10px 0 22px 0; font-size: 16px; font-style: normal; font-weight: 500; line-height: 130%;">Han transcurrido <span style="color: #FFD453;">10 minutos</span> desde que un huésped ha brindado un feedback acerca de su experiencia en el <?php echo e($hotel->type); ?> <?php echo e($hotel->name); ?>. Responde cuanto antes.</span>
                        <?php else: ?>
                            <span style="font-size: 32px; font-style: normal; font-weight: 600; line-height: 110%; margin: 0;">Tienes un nuevo feedback</span>
                            <span style="display: block; margin: 10px 0 22px 0; font-size: 16px; font-style: normal; font-weight: 500; line-height: 130%;">Un huésped ha brindado un feedback acerca de su experiencia en tu <?php echo e($hotel->type); ?> <?php echo e($hotel->name); ?>.</span>
                        <?php endif; ?>
                        <!-- Botón que se muestra en el modo no responsive -->
                        <div style="text-align: center; margin-top: 22px;">
                            <a href="<?php echo e($url); ?>" class="full-width-button order-3" style="display: inline-block; padding: 12px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 500; width: 100%; height: 45px; box-sizing: border-box;">Atender Feedback</a>
                        </div>
                    </td>
                    <td class="order-2" style="text-align: center; width: 30%; vertical-align: top;">
                        <?php if($type == 'pending'): ?>
                            <img src="<?php echo e(asset('mails/feedback-pending.png')); ?>" alt="Feedback Image" style="width: 227px; height: 240px;">
                        <?php else: ?>
                            <img src="<?php echo e(asset('mails/feedback.png')); ?>" alt="Feedback Image" style="width: 227px; height: 240px;">
                        <?php endif; ?>
                        <!-- Botón que se muestra en el modo responsive -->
                        <div style="text-align: center; margin-top: 22px;">
                            <a href="<?php echo e($url); ?>" class="full-width-button order-4" style="display: inline-block; padding: 12px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 500; width: 100%; height: 45px; box-sizing: border-box;">Atender Feedback</a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Nueva sección añadida aquí -->
        <div style="background-color: white; padding: 20px; text-align: left;">
            <span style="font-size: 24px; font-style: normal; font-weight: 600; line-height: 110%; margin: 0;">Feedback en Stay</span>
            <span style="display: block; margin: 10px 0; font-weight: 600; font-size: 16px;"><?php echo e($guest->name); ?></span>
            <span style="display: block; margin: 10px 0; font-weight: 400; font-size: 16px;">
                <img src="<?php echo e(asset('mails/icons/flags/png/'.$query->response_lang.'.png')); ?>" alt="Idioma original" style="vertical-align: middle; margin-right: 5px;">
                Idioma original: <span style="font-weight: 400;"><?php echo e($languageName); ?></span>
            </span>
            <span style="display: block; margin: 10px 0; font-weight: 600; font-size: 16px;">¿Cómo calificarías tu nivel de satisfacción con tu estancia hasta ahora?</span>
            <div style="display: flex; align-items: center; margin: 10px 0;">
                <img src="<?php echo e(asset('mails/icons/reviews/'.$query->qualification.'.png')); ?>" alt="Satisfacción" style="width: 24px; height: 24px; margin-right: 10px;">
                <span style="display: block; font-weight: 400; font-size: 16px; margin: 0;"><?php echo e($query->comment[$query->response_lang] ?? $query->comment['SinTraduccion'] ?? '--'); ?></span>
            </div>
            <div style="text-align: center; margin-top: 22px;">
                <a href="<?php echo e($url); ?>" class="full-width-button" style="display: inline-block; padding: 12px 20px; background-color: #FFD453; color: #000; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: 500; width: 100%; height: 45px; box-sizing: border-box;">Atender feedback</a>
            </div>
            <span style="display: block; margin: 10px 0; color: #A0A0A0; text-align: center; font-size: 14px;">Nota: En la plataforma podrás ver el mensaje en el idioma de elijas</span>
        </div>

        <!-- Footer -->
        <?php echo $__env->make('components.mails.footer',['showNotify' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\api-hoster\resources\views/Mails/queries/NewFeedback.blade.php ENDPATH**/ ?>