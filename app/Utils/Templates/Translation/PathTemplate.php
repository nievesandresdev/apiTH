<?php

namespace App\Utils\Templates\Translation;

class PathTemplate
{
    // Define los valores permitidos para el campo 'dirTemplate'
    public const GENERIC = 'translation/generic';
    public const WEBAPP_FACILITY = 'translation/webapp/facility';
    public const WEBAPP_HOTEL_INPUT_DESCRIPTION = 'translation/webapp/hotel_input/description';

    // Devuelve un array con todos los valores permitidos
    public static function getAllowedTemplates()
    {
        return [
            self::GENERIC,
            self::WEBAPP_FACILITY,
            self::WEBAPP_HOTEL_INPUT_DESCRIPTION,
            // Agrega más PATHS aquí según sea necesario
        ];
    }
}