<?php

namespace App\Utils\Enums\EnumsHotel;

use stdClass;

/**
 * Class EnumResponse
 *
 * @package App\Utils\Enums
 * @author  David Rivero <davidmriverog@gmail.com>
 */
    class ConfigHomeSectionsEnum
    {
        public static function defaultOrderSections(): stdClass
        {
            $fieldsForm = new stdClass();
            $fieldsForm->order = ['facilitiesSection', 'placesRecommendationSection', 'placesExploreSection', 'servicesSection', 'activitiesSection', 'socialNetworksSection'];
            $fieldsForm->buttonsSection = [
                "visibility" => true,
                "style" => 1
            ];
            $fieldsForm->facilitiesSection = [
                "visibility" => true,
                "style" => 1
            ];
            $fieldsForm->placesRecommendationSection = [
                "visibility" => true,
                "style" => 1
            ];
            $fieldsForm->placesExploreSection = [
                "visibility" => true,
                "style" => 1
            ];
            $fieldsForm->servicesSection = [
                "visibility" => true,
                "style" => 1
            ];
            $fieldsForm->activitiesSection = [
                "visibility" => true,
                "style" => 1
            ];
            $fieldsForm->socialNetworksSection = [
                "visibility" => true,
                "style" => 1
            ];

            return $fieldsForm;
        }
    }