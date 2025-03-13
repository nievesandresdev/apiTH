<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hotelsFiltered = $this->hotels()
            ->where('del', 0)
            ->select('id', 'subdomain') // si quieres sólo estas columnas
            ->get();
        
        // Tomamos los subdominios
        $hotelsSubdomains = $hotelsFiltered->pluck('subdomain');
        
        // Tomamos el primer subdominio (si existe)
        $firstHotelSubdomain = $hotelsSubdomains->first();

        return [
            'id'   => $this->id,
            'subdomain' => $this->subdomain,
            'type' => $this->type,

            // Si es "INDEPENDENT" y existe un hotel, usamos su subdominio
            'independentSubdomain' => $this->type === 'INDEPENDENT' && $firstHotelSubdomain
                ? $firstHotelSubdomain
                : null,

            // Evitas error si $this->subdomainActive es null
            'length_subdomain_default' => optional($this->subdomainActive)->length_subdomain_default,

            // Retornamos la colección de subdominios (o array si lo prefieres con ->all())
            'hotels_subdomains' => $hotelsSubdomains,
        ];
    }
}
