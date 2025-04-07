<?php

namespace App\Http\Controllers\Api\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Services\LegalServices;
use PDF;

class LegalPolicyController extends Controller
{
    protected $legalServices;

    public function __construct(
        LegalServices $legalServices
    )
    {
        $this->legalServices = $legalServices;
    }

    public function getPolicyLegal() {
        $hotel = request()->attributes->get('hotel');
        $perPage = request()->get('per_page', 2); // número predeterminado de elementos por página
        $page = request()->get('page', 1); // página predeterminada

        $data = $this->legalServices->getPolicyLegal($hotel, $perPage, $page);

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'policies' => $data->items(),
            //'hotel' => $hotel
        ]);
    }


    public function storePolicylLegal()
    {
        $hotel = request()->attributes->get('hotel');

        try {
            $data = $this->legalServices->storeLegalPolicies($hotel, request()->all());

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'policies' => $data,
            ]);
        } catch (\Exception $e) {

            return bodyResponseRequest(EnumResponse::INTERNAL_SERVER_ERROR, $e->getMessage(), 'Se encontró un error durante la operación', get_class($e));
        }
    }

    public function updatePolicylLegal()
    {
        try {
            $data = $this->legalServices->updateLegalPolicies(request()->all());

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'policies' => $data,
            ]);
        } catch (\Exception $e) {

            return bodyResponseRequest(EnumResponse::INTERNAL_SERVER_ERROR, $e->getMessage(), 'Se encontró un error durante la operación', get_class($e));
        }
    }

    public function deletePolicylLegal()
    {
        try {
            $data = $this->legalServices->deleteLegalPolicies(request()->all());

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'policies' => $data,
            ]);
        } catch (\Exception $e) {

            return bodyResponseRequest(EnumResponse::INTERNAL_SERVER_ERROR, $e->getMessage(), 'Se encontró un error durante la operación', get_class($e));
        }
    }

    public function getCountPoliciesByHotel()
    {
        $hotel = request()->attributes->get('hotel');

        try {
            $data = $this->legalServices->getCountPoliciesByHotel($hotel);

            return bodyResponseRequest(EnumResponse::SUCCESS, $data);
        } catch (\Exception $e) {

            return bodyResponseRequest(EnumResponse::INTERNAL_SERVER_ERROR, $e->getMessage(), 'Se encontró un error durante la operación', get_class($e));
        }
    }
    
    public function generatePDF()
    {
        // Datos dinámicos que serán reemplazados en la vista del PDF
        /* $data = [
            'hotel' => $request->input('hotel', 'Hotel Example'),
            'direccion' => $request->input('direccion', '123 Example Street, City, Country'),
            'nif' => $request->input('nif', 'A12345678'),
            'email' => $request->input('email', 'contact@example.com'),
        ]; */

        // Cargar la vista y pasarle los datos
        $pdf = PDF::loadView('Legal.policy');

        // Retornar el PDF para descarga
        return $pdf->download('Politica_de_Privacidad.pdf');

        // Alternativamente, si solo quieres abrir el PDF en una nueva pestaña:
        // return $pdf->stream('Politica_de_Privacidad.pdf');
    }
}
