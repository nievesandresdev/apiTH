<?php

namespace App\Services;

use App\Models\Legal\LegalGeneral;

class LegalServices {

    public function getGeneralLegal($hotel)
    {
        return $hotel->generalLegal;
    }

    public function storeOrUpdateLegalGeneral($hotel, $data)
    {
        // Utiliza updateOrCreate para buscar y actualizar o crear un nuevo registro
        $legalGeneral = LegalGeneral::updateOrCreate(
            ['hotel_id' => $hotel->id], // Condición para buscar el registro
            [
                'name' => $data['name'],
                'address' => $data['address'],
                'nif' => $data['nif'],
                'email' => $data['email'],
                'protection' => $data['protection'],
                'email_protection' => $data['email_protection'],
            ] // Datos para actualizar o crear el registro
        );

        return $legalGeneral;
    }
}
