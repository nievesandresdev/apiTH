<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
// use App\Services\LanguageServices;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

use App\Models\Language;

class LanguageController extends Controller
{
    public $service;

    function __construct(
        // LanguageServices $_LanguageServices,
    )
    {
        // $this->service = $_LanguageServices;
    }



    public function getAll(Request $request){

        try {
            $isWebapp = isset($request->isWebapp) ? intval($request->isWebapp) : true;
            if ($isWebapp) {
                $data = ['es', 'en', 'fr','pt','it','de'];
                return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
            }

            $languagesModel = Language::all();
            $languagesCollection = LanguageResource::collection($languagesModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $languagesCollection);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    // Nuevo método para obtener idiomas específicos basado en un array de IDs o nombres
    public function getLanguageForItem(Request $request)
    {
        //return bodyResponseRequest(EnumResponse::ACCEPTED, $request->all());
        // Filtramos los idiomas según el array recibido
        $languages = Language::whereIn('abbreviation', $request->languages)->orderBy('name')->get();

        // Ordenamos alfabéticamente y ponemos el "selected" al principio
        $selected = $request->selected_language; // Asumo que el campo "selected_language" se pasa en la solicitud

       /*  $languages = $languages->sortBy(function ($language) use ($selected) {
            // Primero, si el idioma es el seleccionado, lo ponemos en primer lugar
            return $language->abbreviation === $selected ? 0 : 1;
        })->sortBy('name'); // Luego, ordenamos alfabéticamente */

        // Devolvemos la colección de idiomas como un recurso
        return bodyResponseRequest(EnumResponse::ACCEPTED,$languages);
    }





}
