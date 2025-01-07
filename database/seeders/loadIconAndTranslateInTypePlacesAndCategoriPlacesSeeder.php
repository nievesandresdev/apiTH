<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TypePlaces;
use App\Models\CategoriPlaces;

class loadIconAndTranslateInTypePlacesAndCategoriPlacesSeeder extends Seeder
{
    public function run(): void
    {
        $iconByNameInTypePlaces = [
            [
                'name' => 'Qué visitar',
                'icon' => 'WA.MONUMENTOS',
                'translation' => [
                    'es' => 'Qué visitar',
                    'en' => 'What to visit',
                    'fr' => 'Que visiter',
                    'pr' => 'O que visitar',
                    'de' => 'Was besichtigen',
                    'it' => 'Cosa visitare',
                ]
            ],
            [
                'name' => 'Dónde comer',
                'icon' => 'WA.RESTAURANTES',
                'translation' => [
                    'es' => 'Dónde comer',
                    'en' => 'Where to eat',
                    'fr' => 'Où manger',
                    'pr' => 'Onde comer',
                    'de' => 'Wo essen',
                    'it' => 'Dove mangiare',
                ]
            ],
            [
                'name' => 'Ocio',
                'icon' => 'WA.OCIO',
                'translation' => [
                    'es' => 'Ocio',
                    'en' => 'Leisure',
                    'fr' => 'Loisir',
                    'pr' => 'Lazer',
                    'de' => 'Freizeit',
                    'it' => 'Svago',
                ]
                
            ],
        ];

        foreach ($iconByNameInTypePlaces as $typePlace) {
            $typePlaceModel = TypePlaces::where('name', $typePlace['name'])->first();
            if ($typePlaceModel){
                $typePlaceModel->update(['icon' => $typePlace['icon'], 'translate' => json_encode($typePlace['translation'])]);
            }
        }

        $iconByNameInCategoriPlaces = [
            [
                'name' => 'Monumentos',
                'icon' => 'WA.MONUMENTOS',
                'translation' => [
                    'es' => 'Monumentos',
                    'en' => 'Monuments',
                    'fr' => 'Monuments',
                    'pr' => 'Monumentos',
                    'de' => 'Denkmäler',
                    'it' => 'Monumenti',
                ]
            ],
            [
                'name' => 'Museos',
                'icon' => 'WA.MUSEOS',
                'translation' => [
                    'es' => 'Museos',
                    'en' => 'Museums',
                    'fr' => 'Musées',
                    'pr' => 'Museus',
                    'de' => 'Museen',
                    'it' => 'Musei',
                ]
            ],
            [
                'name' => 'Naturaleza',
                'icon' => 'WA.NATURALEZA',
                'translation' => [
                    'es' => 'Naturaleza',
                    'en' => 'Nature',
                    'fr' => 'Nature',
                    'pr' => 'Natureza',
                    'de' => 'Natur',
                    'it' => 'Natura',
                ]
            ],
            [
                'name' => 'Cafeterías y postres',
                'icon' => 'WA.COFFE',
                'translation' => [
                    'es' => 'Cafeterías y postres',
                    'en' => 'Cafes and desserts',
                    'fr' => 'Cafés et desserts',
                    'pr' => 'Cafés e sobremesas',
                    'de' => 'Cafés und Desserts',
                    'it' => 'Caffè e dessert',
                ]
            ],
            [
                'name' => 'Restaurantes',
                'icon' => 'WA.RESTAURANTES',
                'translation' => [
                    'es' => 'Restaurantes',
                    'en' => 'Restaurants',
                    'fr' => 'Restaurants',
                    'pr' => 'Restaurantes',
                    'de' => 'Restaurants',
                    'it' => 'Ristoranti',
                ]
            ],
            [
                'name' => 'Vida nocturna',
                'icon' => 'WA.VIDANOCTURNA',
                'translation' => [
                    'es' => 'Vida nocturna',
                    'en' => 'Nightlife',
                    'fr' => 'Vie nocturne',
                    'pr' => 'Vida noturna',
                    'de' => 'Nachtleben',
                    'it' => 'Vita notturna',
                ]
            ],
            [
                'name' => 'Compras',
                'icon' => 'WA.OCIO',
                'translation' => [
                    'es' => 'Compras',
                    'en' => 'Shopping',
                    'fr' => 'Achats',
                    'pr' => 'Compras',
                    'de' => 'Einkaufen',
                    'it' => 'Shopping',
                ]
            ],
            [
                'name' => 'Otros',
                'icon' => 'WA.OTROS',
                'translation' => [
                    'es' => 'Otros',
                    'en' => 'Others',
                    'fr' => 'Autres',
                    'pr' => 'Outros',
                    'de' => 'Andere',
                    'it' => 'Altri',
                ]
            ],
        ];

        foreach ($iconByNameInCategoriPlaces as $categoriPlace) {
            $categoriPlaceModel = CategoriPlaces::where('name', $categoriPlace['name'])->first();
            if ($categoriPlaceModel){
                $categoriPlaceModel->update(['icon' => $categoriPlace['icon'], 'translate' => json_encode($categoriPlace['translation'])]);
            }
        }
        
    }
}
