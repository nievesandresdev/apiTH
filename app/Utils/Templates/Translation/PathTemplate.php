<?php

namespace App\Utils\Templates\Translation;

class PathTemplate
{
    // Define los valores permitidos para el campo 'dirTemplate'
    public const GENERIC = 'translation/generic';
    public const WEBAPP_HOTEL_INPUT_EXPERIENCE = 'translation/webapp/hotel_input/experience';
    public const WEBAPP_HOTEL_INPUT_PLACE = 'translation/webapp/hotel_input/place';
    public const WEBAPP_HOTEL_INPUT_FACILITY = 'translation/webapp/hotel_input/facility';
    public const WEBAPP_HOTEL_INPUT_DESCRIPTION = 'translation/webapp/hotel_input/description';
    
    public const WEBAPP_PLACE_DYNAMIC_VALUE = 'translation/webapp/place/dynamic_value';

    // Devuelve un array con todos los valores permitidos
    public static function getAllowedTemplates()
    {
        return [
            self::GENERIC,
            self::WEBAPP_HOTEL_INPUT_FACILITY,
            self::WEBAPP_HOTEL_INPUT_DESCRIPTION,
            self::WEBAPP_HOTEL_INPUT_EXPERIENCE,
            self::WEBAPP_HOTEL_INPUT_PLACE,
            self::WEBAPP_PLACE_DYNAMIC_VALUE,
            // Agrega más PATHS aquí según sea necesario
        ];
    }
}