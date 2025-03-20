<?php

namespace App\Utils\Enums\EnumsStay;

use stdClass;

/**
 * Class EnumResponse
 *
 * @package App\Utils\Enums
 * @author  David Rivero <davidmriverog@gmail.com>
 */
    class CheckinSettingsDefaultEnum
    {
        public static function defaultFieldsByFirstStep(): stdClass
        {
            // Creamos el objeto principal
            $fieldsForm = new stdClass();
            //
            $fieldsForm->name = new stdClass();
            $fieldsForm->name->visible = true;
            $fieldsForm->name->mandatory = true;
            $fieldsForm->name->dependence = false;
            //
            $fieldsForm->lastname = new stdClass();
            $fieldsForm->lastname->visible = true;
            $fieldsForm->lastname->mandatory = true;
            $fieldsForm->lastname->dependence = false;
            //
            $fieldsForm->secondLastname = new stdClass();
            $fieldsForm->secondLastname->visible = false;
            $fieldsForm->secondLastname->mandatory = true;
            $fieldsForm->secondLastname->dependence = true;
            //
            $fieldsForm->birthdate = new stdClass();
            $fieldsForm->birthdate->visible = true;
            $fieldsForm->birthdate->mandatory = true;
            $fieldsForm->birthdate->dependence = false;
            //
            $fieldsForm->gender = new stdClass();
            $fieldsForm->gender->visible = true;
            $fieldsForm->gender->mandatory = false;
            $fieldsForm->gender->dependence = false;
            //
            $fieldsForm->phone = new stdClass();
            $fieldsForm->phone->visible = true;
            $fieldsForm->phone->mandatory = false;
            $fieldsForm->phone->dependence = false;
            //
            $fieldsForm->email = new stdClass();
            $fieldsForm->email->visible = true;
            $fieldsForm->email->mandatory = true;
            $fieldsForm->email->dependence = false;
            //
            $fieldsForm->responsibleAdult = new stdClass();
            $fieldsForm->responsibleAdult->visible = false;
            $fieldsForm->responsibleAdult->mandatory = false;
            $fieldsForm->responsibleAdult->dependence = true;
            //
            $fieldsForm->kinshipRelationship = new stdClass();
            $fieldsForm->kinshipRelationship->visible = false;
            $fieldsForm->kinshipRelationship->mandatory = false;
            $fieldsForm->kinshipRelationship->dependence = true;
            return $fieldsForm;
        }

        public static function defaultFieldsBySecondStep(): stdClass
        {
            // Creamos el objeto principal
            $fieldsForm = new stdClass();
            //
            $fieldsForm->nationality = new stdClass();
            $fieldsForm->nationality->visible = true;
            $fieldsForm->nationality->mandatory = false;
            $fieldsForm->nationality->dependence = false;
            //
            $fieldsForm->docType = new stdClass();
            $fieldsForm->docType->visible = true;
            $fieldsForm->docType->mandatory = true;
            $fieldsForm->docType->dependence = false;
            //
            $fieldsForm->docSupportNumber = new stdClass();
            $fieldsForm->docSupportNumber->visible = false;
            $fieldsForm->docSupportNumber->mandatory = true;
            $fieldsForm->docSupportNumber->dependence = true;
            //
            $fieldsForm->docNumber = new stdClass();
            $fieldsForm->docNumber->visible = true;
            $fieldsForm->docNumber->mandatory = true;
            $fieldsForm->docNumber->dependence = false;
            //
            $fieldsForm->countryResidence = new stdClass();
            $fieldsForm->countryResidence->visible = true;
            $fieldsForm->countryResidence->mandatory = true;
            $fieldsForm->countryResidence->dependence = false;
            //
            $fieldsForm->postalCode = new stdClass();
            $fieldsForm->postalCode->visible = true;
            $fieldsForm->postalCode->mandatory = true;
            $fieldsForm->postalCode->dependence = false;
            //
            $fieldsForm->municipality = new stdClass();
            $fieldsForm->municipality->visible = true;
            $fieldsForm->municipality->mandatory = true;
            $fieldsForm->municipality->dependence = false;
            //
            $fieldsForm->addressResidence = new stdClass();
            $fieldsForm->addressResidence->visible = true;
            $fieldsForm->addressResidence->mandatory = true;
            $fieldsForm->addressResidence->dependence = false;
            return $fieldsForm;
        }

        public static function defaultFieldsForm(): stdClass
        {
            $fieldsForm = new stdClass();
            $fieldsForm->succes_message = [
                "es" => "<p>Estamos encantados de darte la bienvenida y esperamos que disfrutes de una estancia inolvidable con nosotros.</p><p>Si necesitas algo antes o durante tu visita, no dudes en hacérnoslo saber.</p><p>¡Te esperamos pronto!</p>",            
                "en" => "<p>We are delighted to welcome you and hope you enjoy an unforgettable stay with us.</p><p>If you need anything before or during your visit, please let us know.</p><p>We look forward to seeing you soon!</p>",
                "fr" => "<p>Nous sommes ravis de vous accueillir et nous espérons que vous profiterez d'un séjour inoubliable parmi nous.</p><p>Si vous avez besoin de quelque chose avant ou pendant votre visite, n'hésitez pas à nous le faire savoir.</p><p>Nous avons hâte de vous revoir bientôt !</p>",
                "pt" => "<p>Estamos encantados em recebê-lo(a) e esperamos que você desfrute de uma estadia inesquecível conosco.</p><p>Se precisar de algo antes ou durante a sua visita, não hesite em nos informar.</p><p>Esperamos vê-lo(a) em breve!</p>",
                "it" => "<p>Siamo lieti di darti il benvenuto e speriamo che tu possa godere di un soggiorno indimenticabile con noi.</p><p>Se hai bisogno di qualcosa prima o durante il tuo soggiorno, non esitare a farcelo sapere.</p><p>Non vediamo l'ora di vederti presto!</p>",
                "de" => "<p>Wir freuen uns, Sie begrüßen zu dürfen und hoffen, dass Sie einen unvergesslichen Aufenthalt bei uns genießen.</p><p>Sollten Sie vor oder während Ihres Aufenthalts etwas benötigen, zögern Sie bitte nicht, es uns mitzuteilen.</p><p>Wir freuen uns darauf, Sie bald wiederzusehen!</p>",
                "ca" => "<p>Estem encantats de donar-te la benvinguda i esperem que gaudeixis d'una estada inoblidable amb nosaltres.</p><p>Si necessites res abans o durant la teva visita, no dubtis en fer-nos-ho saber.</p><p>Et esperem aviat!</p>",
                "eu" => "<p>Ongi etorri esatea pozik gaude egiten diogu eta espero dugu gurekin egindako egonaldia ahaztezina izango dela.</p><p>Bisita aurretik edo bitartean zerbait behar baduzu, mesedez jakinarazi iezaguzu.</p><p>Laster elkartuko gara!</p>",
                "gl" => "<p>Estamos encantados de darche a benvida e esperamos que desfrutes dunha estadía inesquecible connosco.</p><p>Se precisas algo antes ou durante a túa visita, non dubides en facernoso saber.</p><p>Agardámoste pronto!</p>",
                "nl" => "<p>We zijn verheugd je te mogen verwelkomen en hopen dat je een onvergetelijk verblijf bij ons zult hebben.</p><p>Als je iets nodig hebt voor of tijdens je bezoek, laat het ons dan weten.</p><p>We hopen je snel te zien!</p>"
            ];
            $fieldsForm->first_step = self::defaultFieldsByFirstStep();
            $fieldsForm->second_step = self::defaultFieldsBySecondStep();
            $fieldsForm->show_prestay_query = true;
            return $fieldsForm;
        }
    }