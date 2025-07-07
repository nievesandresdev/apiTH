<?php

namespace App\Utils\Enums\EnumsQueries;
use stdClass;
class QuerySettingsEnums
{
    
    public static function preStayqueriesTextDefault(): stdClass
    {
        $queriesTextDefault = new stdClass();
        $queriesTextDefault->pre_stay_activate = false;
        $queriesTextDefault->pre_stay_comment = [
            "es" => "Agradecemos tu respuesta. Nos importa tu experiencia y queremos cumplir tus expectativas.",
            "en" => "We appreciate your response. We care about your experience and we want to meet your expectations.",
            "fr" => "Nous apprécions votre réponse. Nous nous soucions de votre expérience et nous voulons répondre à vos attentes.",
            "pt" => "Agradecemos sua resposta. Nos importa sua experiência e queremos cumprir suas expectativas.",
            "it" => "Apprezziamo la tua risposta. Ci importa la tua esperienza e vogliamo soddisfare le tue aspettative.",
            "de" => "Wir schätzen Ihre Antwort. Wir kümmern uns um Ihre Erfahrung und wollen Ihre Erwartungen erfüllen.",
            "ca" => "Agraïm la teva resposta. Ens importa la teva experiència i volem complir les teves expectatives.",
            "eu" => "Zure erantzuna onuragarria da. Zure esperientzia garrantzitsua zaigu eta zure itxaropenak betetzeko ahalegintzen gara.",
            "gl" => "Agradecemos a túa resposta. Importa a túa experiencia e queremos cumprir as túas expectativas.",
            "nl" => "We waarderen je antwoord. We geven om je ervaring en we willen aan je verwachtingen voldoen."
        ];
        return $queriesTextDefault;
    }
    
    public static function inStayqueriesTextDefault(): stdClass
    {
        $queriesTextDefault = new stdClass();
        $queriesTextDefault->in_stay_verygood_request_activate = true;
        $queriesTextDefault->in_stay_verygood_response_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];
        $queriesTextDefault->in_stay_verygood_response_msg = [
            "es" => '<p>Tu experiencia es muy importante, y puede ayudar a otros viajeros a conocernos.</p><p>¿Nos dejas tu reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and it can help other travelers to know us.</p><p>Would you leave us your review?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et elle peut aider d'autres voyageurs à nous connaître.</p><p>Voudriez-vous nous laisser votre avis ?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e pode ajudar outros viajantes a nos conhecerem.</p><p>Você nos deixaria sua redação?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e può aiutare altri viaggiatori a conoscerci.</p><p>Ti lasceresti il tuo commento?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig und kann anderen Reisenden helfen, uns kennen zu lernen.</p><p>Möchten Sie uns Ihre Bewertung hinterlassen?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i pot ajudar altres viatgers a conèixer-nos.</p><p>Voldria deixar-nos el teu comentari?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta beste errebetariak gurekin bat egin dezaketen.</p><p>Zure iritzia utzi ditzakezu?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e pode axudar outros viaxeiros a coñecernos.</p><p>¿Deixarías a túa reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en kan andere reizigers helpen om ons te leren kennen.</p><p>Wilt u ons uw beoordeling achterlaten?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        $queriesTextDefault->in_stay_verygood_request_otas = [
            "booking" => false,
            "expedia" => false,
            "google" => true,
            "tripadvisor" => true,
            "airbnb" => false
        ];
        $queriesTextDefault->in_stay_verygood_no_request_comment_activate = true;
        $queriesTextDefault->in_stay_verygood_no_request_comment_msg = [
            "es" => "¿Quieres dejarnos un comentario?",
            "en" => "Would you like to leave us a comment?",
            "fr" => "Voudriez-vous nous laisser un commentaire ?",
            "pt" => "Você gostaria de nos deixar um comentário?",
            "it" => "Ti piacerebbe lasciarci un commento?",
            "de" => "Möchten Sie uns einen Kommentar hinterlassen?",
            "ca" => "Voldria deixar-nos un comentari?",
            "eu" => "Zure iritzia utzi ditzakezu?",
            "gl" => "¿Deixarías a túa reseña?",
            "nl" => "Wilt u ons een beoordeling achterlaten?"
        ];
        $queriesTextDefault->in_stay_verygood_no_request_thanks_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];
        $queriesTextDefault->in_stay_verygood_no_request_thanks_msg = [
            "es" => '<p>Tu experiencia es muy importante, y nos alegra saber que estás disfrutando de tu estancia.</p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and we are glad to know that you are enjoying your stay.</p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et nous sommes heureux de savoir que vous appréciez votre séjour.</p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e estamos felizes em saber que você está aproveitando sua estadia.</p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e ci rendiamo felice di sapere che stai godendo della tua permanenza.</p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig, und wir freuen uns, dass Sie Ihren Aufenthalt genießen.</p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i ens alegram de saber que estàs gaudint de la teva estada.</p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta zure egonaldia gozatzen duzula jakiteak ahalegintzen gara.</p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e estamos felices de saber que estás aproveitando a túa estancia.</p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en we zijn blij dat je van je verblijf geniet.</p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        //////////////////////
        $queriesTextDefault->in_stay_good_request_activate = true;
        $queriesTextDefault->in_stay_good_response_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];










        $queriesTextDefault->in_stay_good_response_msg = [
            "es" => '<p>Tu experiencia es muy importante, y puede ayudar a otros viajeros a conocernos.</p><p>¿Nos dejas tu reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and it can help other travelers to know us.</p><p>Would you leave us your review?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et elle peut aider d'autres voyageurs à nous connaître.</p><p>Voudriez-vous nous laisser votre avis ?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e pode ajudar outros viajantes a nos conhecerem.</p><p>Você nos deixaria sua redação?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e può aiutare altri viaggiatori a conoscerci.</p><p>Ti lasceresti il tuo commento?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig und kann anderen Reisenden helfen, uns kennen zu lernen.</p><p>Möchten Sie uns Ihre Bewertung hinterlassen?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i pot ajudar altres viatgers a conèixer-nos.</p><p>Voldria deixar-nos el teu comentari?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta beste errebetariak gurekin bat egin dezaketen.</p><p>Zure iritzia utzi ditzakezu?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e pode axudar outros viaxeiros a coñecernos.</p><p>¿Deixarías a túa reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en kan andere reizigers helpen om ons te leren kennen.</p><p>Wilt u ons uw beoordeling achterlaten?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        $queriesTextDefault->in_stay_good_request_otas = [
            "booking" => false,
            "expedia" => false,
            "google" => true,
            "tripadvisor" => true,
            "airbnb" => false
        ];
        $queriesTextDefault->in_stay_good_no_request_comment_activate = true;
        $queriesTextDefault->in_stay_good_no_request_comment_msg = [
            "es" => "¿Quieres dejarnos un comentario?",
            "en" => "Would you like to leave us a comment?",
            "fr" => "Voudriez-vous nous laisser un commentaire ?",
            "pt" => "Você gostaria de nos deixar um comentário?",
            "it" => "Ti piacerebbe lasciarci un commento?",
            "de" => "Möchten Sie uns einen Kommentar hinterlassen?",
            "ca" => "Voldria deixar-nos un comentari?",
            "eu" => "Zure iritzia utzi ditzakezu?",
            "gl" => "¿Deixarías a túa reseña?",
            "nl" => "Wilt u ons een beoordeling achterlaten?"
        ];
        $queriesTextDefault->in_stay_good_no_request_thanks_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];
        $queriesTextDefault->in_stay_good_no_request_thanks_msg = [
            "es" => '<p>Tu experiencia es muy importante, y nos alegra saber que estás disfrutando de tu estancia.</p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and we are glad to know that you are enjoying your stay.</p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et nous sommes heureux de savoir que vous appréciez votre séjour.</p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e estamos felizes em saber que você está aproveitando sua estadia.</p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e ci rendiamo felice di sapere che stai godendo della tua permanenza.</p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig, und wir freuen uns, dass Sie Ihren Aufenthalt genießen.</p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i ens alegram de saber que estàs gaudint de la teva estada.</p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta zure egonaldia gozatzen duzula jakiteak ahalegintzen gara.</p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e estamos felices de saber que estás aproveitando a túa estancia.</p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en we zijn blij dat je van je verblijf geniet.</p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        //////////////////////
        $queriesTextDefault->in_stay_bad_response_title = [
            "es" => "¡Lo sentimos mucho [nombreHuesped]!",
            "en" => "¡We are sorry [nombreHuesped]!",
            "fr" => "Désolé [nombreHuesped]!",
            "pt" => "Desculpe [nombreHuesped]!",
            "it" => "Siamo spiacenti [nombreHuesped]!",
            "de" => "Wir sind leider [nombreHuesped]!",
            "ca" => "Ho sentim molt [nombreHuesped]!",
            "eu" => "Barkatu [nombreHuesped]!",
            "gl" => "Sentimos muito [nombreHuesped]!",
            "nl" => "Sorry [nombreHuesped]!"
        ];
        $queriesTextDefault->in_stay_bad_response_msg = [
            "es" => "Sentimos mucho no haber cumplido con tus expectativas. Hemos recibido tus comentarios y trabajaremos para solucionarlo. ¡Gracias por ayudarnos a mejorar!",
            "en" => "We are sorry we did not meet your expectations. We have received your comments and we will work to solve it. Thank you for helping us improve!",
            "fr" => "Nous sommes désolés de ne pas avoir répondu à vos attentes. Nous avons reçu vos commentaires et nous travaillerons pour les résoudre. Merci d'avoir contribué à notre amélioration!",
            "pt" => "Sentimos muito não ter cumprido com suas expectativas. Recebemos seus comentários e trabalharemos para solucioná-los. Obrigado por nos ajudar a melhorar!",
            "it" => "Siamo spiacenti di non aver soddisfatto le tue aspettative. Abbiamo ricevuto i tuoi commenti e lavoreremo per risolverli. Grazie per averci aiutato a migliorare!", 
            "de" => "Wir haben Ihre Kommentare erhalten und werden uns daran arbeiten, sie zu lösen. Danke für Ihre Hilfe, um uns zu verbessern!",
            "ca" => "Sentim molt que no hem complert les teves expectatives. Hem rebut els teus comentaris i treballarem per resoldre'ls. Gràcies per ajudar-nos a millorar!",
            "eu" => "Zure iritziak betetzeko ahalegintzen gara. Jaso dituzun iritziak eta egingo dugu ebatzi. Eskerrik asko laguntzeko!",
            "gl" => "Sentimos moito que non cumprimos as túas expectativas. Recibimos os teus comentarios e traballaremos para resolverlos. Grazas por axudarnos a mellorar!",
            "nl" => "We zijn verontwaardigd dat we niet aan uw verwachtingen hebben voldaan. We hebben uw opmerkingen ontvangen en zullen er aan werken om ze op te lossen. Bedankt voor uw hulp om ons te verbeteren!"
        ];
        return $queriesTextDefault;
    }

    public static function postStayqueriesTextDefault(): stdClass
    {
        $queriesTextDefault = new stdClass();
        $queriesTextDefault->post_stay_verygood_response_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];
        $queriesTextDefault->post_stay_verygood_response_msg = [
            "es" => '<p>Tu experiencia es muy importante, y puede ayudar a otros viajeros a conocernos.</p><p>¿Nos dejas tu reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and it can help other travelers to know us.</p><p>Would you leave us your review?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et elle peut aider d'autres voyageurs à nous connaître.</p><p>Voudriez-vous nous laisser votre avis ?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e pode ajudar outros viajantes a nos conhecerem.</p><p>Você nos deixaria sua redação?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e può aiutare altri viaggiatori a conoscerci.</p><p>Ti lasceresti il tuo commento?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig und kann anderen Reisenden helfen, uns kennen zu lernen.</p><p>Möchten Sie uns Ihre Bewertung hinterlassen?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i pot ajudar altres viatgers a conèixer-nos.</p><p>Voldria deixar-nos el teu comentari?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta beste errebetariak gurekin bat egin dezaketen.</p><p>Zure iritzia utzi ditzakezu?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e pode axudar outros viaxeiros a coñecernos.</p><p>¿Deixarías a túa reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en kan andere reizigers helpen om ons te leren kennen.</p><p>Wilt u ons uw beoordeling achterlaten?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        $queriesTextDefault->post_stay_verygood_request_otas = [
            "booking" => true,
            "expedia" => false,
            "google" => true,
            "tripadvisor" => true,
            "airbnb" => false
        ];
        //////////////////////
        $queriesTextDefault->post_stay_good_request_activate = true;
        $queriesTextDefault->post_stay_good_response_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];
        $queriesTextDefault->post_stay_good_response_msg = [
            "es" => '<p>Tu experiencia es muy importante, y puede ayudar a otros viajeros a conocernos.</p><p>¿Nos dejas tu reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and it can help other travelers to know us.</p><p>Would you leave us your review?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et elle peut aider d'autres voyageurs à nous connaître.</p><p>Voudriez-vous nous laisser votre avis ?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e pode ajudar outros viajantes a nos conhecerem.</p><p>Você nos deixaria sua redação?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e può aiutare altri viaggiatori a conoscerci.</p><p>Ti lasceresti il tuo commento?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig und kann anderen Reisenden helfen, uns kennen zu lernen.</p><p>Möchten Sie uns Ihre Bewertung hinterlassen?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i pot ajudar altres viatgers a conèixer-nos.</p><p>Voldria deixar-nos el teu comentari?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta beste errebetariak gurekin bat egin dezaketen.</p><p>Zure iritzia utzi ditzakezu?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e pode axudar outros viaxeiros a coñecernos.</p><p>¿Deixarías a túa reseña?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en kan andere reizigers helpen om ons te leren kennen.</p><p>Wilt u ons uw beoordeling achterlaten?</p><p><br></p><p><strong>[Enlaces a OTAs]</strong></p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        $queriesTextDefault->post_stay_good_request_otas = [
            "booking" => true,
            "expedia" => false,
            "google" => true,
            "tripadvisor" => true,
            "airbnb" => false
        ];
        $queriesTextDefault->post_stay_good_no_request_comment_activate = true;
        $queriesTextDefault->post_stay_good_no_request_comment_msg = [
            "es" => "¿Quieres dejarnos un comentario?",
            "en" => "Would you like to leave us a comment?",
            "fr" => "Voudriez-vous nous laisser un commentaire ?",
            "pt" => "Você gostaria de nos deixar um comentário?",
            "it" => "Ti piacerebbe lasciarci un commento?",
            "de" => "Möchten Sie uns einen Kommentar hinterlassen?",
            "ca" => "Voldria deixar-nos un comentari?",
            "eu" => "Zure iritzia utzi ditzakezu?",
            "gl" => "¿Deixarías a túa reseña?",
            "nl" => "Wilt u ons een beoordeling achterlaten?"
        ];
        $queriesTextDefault->post_stay_good_no_request_thanks_title = [
            "es" => "¡Muchas gracias [nombreHuesped]!",
            "en" => "¡Muchas gracias [nombreHuesped]!",
            "fr" => "Merci beaucoup [nombreHuesped]!",
            "pt" => "Muito obrigado [nombreHuesped]!",
            "it" => "Molto grazie [nombreHuesped]!",
            "de" => "Vielen Dank [nombreHuesped]!",
            "ca" => "Moltes gràcies [nombreHuesped]!",
            "eu" => "Eskerrik asko [nombreHuesped]!",
            "gl" => "Moitos grazas [nombreHuesped]!",
            "nl" => "Bedankt [nombreHuesped]!"
        ];
        $queriesTextDefault->post_stay_good_no_request_thanks_msg = [
            "es" => '<p>Tu experiencia es muy importante, y nos alegra saber que estás disfrutando de tu estancia.</p><p><br></p><p class="ql-align-center">Agradecemos tu tiempo y ¡Gracias por habernos elegido!</p>',
            "en" => '<p>Your experience is very important, and we are glad to know that you are enjoying your stay.</p><p><br></p><p class="ql-align-center">Thank you for your time and ¡Thank you for choosing us!</p>',
            "fr" => "<p>Votre expérience est très importante, et nous sommes heureux de savoir que vous appréciez votre séjour.</p><p><br></p><p class='ql-align-center'>Nous vous remercions de votre temps et ¡Merci d'avoir choisi notre hôtel !</p>",
            "pt" => '<p>Sua experiência é muito importante, e estamos felizes em saber que você está aproveitando sua estadia.</p><p><br></p><p class="ql-align-center">Agradecemos seu tempo e ¡Obrigado por escolher nosso hotel!</p>',
            "it" => '<p>La tua esperienza è molto importante, e ci rendiamo felice di sapere che stai godendo della tua permanenza.</p><p><br></p><p class="ql-align-center">Grazie per il tuo tempo e ¡Grazie per aver scelto il nostro hotel!</p>',
            "de" => '<p>Ihre Erfahrung ist sehr wichtig, und wir freuen uns, dass Sie Ihren Aufenthalt genießen.</p><p><br></p><p class="ql-align-center">Wir danken Ihnen für Ihre Zeit und ¡Danke für Ihre Wahl!</p>',
            "ca" => '<p>La teva experiència és molt important, i ens alegram de saber que estàs gaudint de la teva estada.</p><p><br></p><p class="ql-align-center">Gràcies pel teu temps i ¡Gràcies per triar el nostre hotel!</p>',
            "eu" => '<p>Zure esperientzia da garrantzitsua da, eta zure egonaldia gozatzen duzula jakiteak ahalegintzen gara.</p><p><br></p><p class="ql-align-center">Eskerrik asko denbora eta ¡Eskerrik asko hartu dugu!</p>',
            "gl" => '<p>A túa experiencia é muito importante, e estamos felices de saber que estás aproveitando a túa estancia.</p><p><br></p><p class="ql-align-center">Agradecemos o teu tempo e ¡Grazas por escoller o noso hotel!</p>',
            "nl" => '<p>Je ervaring is zeer belangrijk, en we zijn blij dat je van je verblijf geniet.</p><p><br></p><p class="ql-align-center">Bedankt voor je tijd en ¡Bedankt voor je keuze!</p>'
        ];
        //////////////////////
        $queriesTextDefault->post_stay_bad_response_title = [
            "es" => "¡Lo sentimos mucho [nombreHuesped]!",
            "en" => "¡We are sorry [nombreHuesped]!",
            "fr" => "Désolé [nombreHuesped]!",
            "pt" => "Desculpe [nombreHuesped]!",
            "it" => "Siamo spiacenti [nombreHuesped]!",
            "de" => "Wir sind leider [nombreHuesped]!",
            "ca" => "Ho sentim molt [nombreHuesped]!",
            "eu" => "Barkatu [nombreHuesped]!",
            "gl" => "Sentimos muito [nombreHuesped]!",
            "nl" => "Sorry [nombreHuesped]!"
        ];
        $queriesTextDefault->post_stay_bad_response_msg = [
            "es" => "Sentimos mucho no haber cumplido con tus expectativas. Hemos recibido tus comentarios y trabajaremos para solucionarlo. ¡Gracias por ayudarnos a mejorar!",
            "en" => "We are sorry we did not meet your expectations. We have received your comments and we will work to solve it. Thank you for helping us improve!",
            "fr" => "Nous sommes désolés de ne pas avoir répondu à vos attentes. Nous avons reçu vos commentaires et nous travaillerons pour les résoudre. Merci d'avoir contribué à notre amélioration!",
            "pt" => "Sentimos muito não ter cumprido com suas expectativas. Recebemos seus comentários e trabalharemos para solucioná-los. Obrigado por nos ajudar a melhorar!",
            "it" => "Siamo spiacenti di non aver soddisfatto le tue aspettative. Abbiamo ricevuto i tuoi commenti e lavoreremo per risolverli. Grazie per averci aiutato a migliorare!", 
            "de" => "Wir haben Ihre Kommentare erhalten und werden uns daran arbeiten, sie zu lösen. Danke für Ihre Hilfe, um uns zu verbessern!",
            "ca" => "Sentim molt que no hem complert les teves expectatives. Hem rebut els teus comentaris i treballarem per resoldre'ls. Gràcies per ajudar-nos a millorar!",
            "eu" => "Zure iritziak betetzeko ahalegintzen gara. Jaso dituzun iritziak eta egingo dugu ebatzi. Eskerrik asko laguntzeko!",
            "gl" => "Sentimos moito que non cumprimos as túas expectativas. Recibimos os teus comentarios e traballaremos para resolverlos. Grazas por axudarnos a mellorar!",
            "nl" => "We zijn verontwaardigd dat we niet aan uw verwachtingen hebben voldaan. We hebben uw opmerkingen ontvangen en zullen er aan werken om ze op te lossen. Bedankt voor uw hulp om ons te verbeteren!"
        ];
        return $queriesTextDefault;
    }

    public static function queriesTextDefault(): stdClass
    {
        $queriesTexts1 = self::preStayqueriesTextDefault();
        $queriesTexts2 = self::inStayqueriesTextDefault();
        $queriesTexts3 = self::postStayqueriesTextDefault();
        // $queriesSettingsNotify = queryNotifyDefault();

        // Convertimos los objetos a arrays
        $array1 = get_object_vars($queriesTexts1);
        $array2 = get_object_vars($queriesTexts2);
        $array3 = get_object_vars($queriesTexts3);
        // $array4 = get_object_vars($queriesSettingsNotify);

        // Fusionamos los arrays
        $mergedArray = array_merge($array1, $array2, $array3);

        // Convertimos el array resultante de nuevo a un objeto
        return (object)$mergedArray;
    }
}