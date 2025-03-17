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
                    'pt' => 'O que visitar',
                    'de' => 'Was besichtigen',
                    'it' => 'Cosa visitare',
                    'ca' => 'Què visitar',
                    'eu' => 'Zer bisitatu',
                    'gl' => 'Que visitar',
                    'nl' => 'Wat te bezoeken',
                ]
            ],
            [
                'name' => 'Dónde comer',
                'icon' => 'WA.RESTAURANTES',
                'translation' => [
                    'es' => 'Dónde comer',
                    'en' => 'Where to eat',
                    'fr' => 'Où manger',
                    'pt' => 'Onde comer',
                    'de' => 'Wo essen',
                    'it' => 'Dove mangiare',
                    'ca' => 'On menjar',
                    'eu' => 'Non jan',
                    'gl' => 'Onde comer',
                    'nl' => 'Waar te eten',
                ]
            ],
            [
                'name' => 'Ocio',
                'icon' => 'WA.OCIO',
                'translation' => [
                    'es' => 'Ocio',
                    'en' => 'Leisure',
                    'fr' => 'Loisir',
                    'pt' => 'Lazer',
                    'de' => 'Freizeit',
                    'it' => 'Svago',
                    'ca' => 'Oci',
                    'eu' => 'Aisia',
                    'gl' => 'Lecer',
                    'nl' => 'Vrije tijd',
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
                    'pt' => 'Monumentos',
                    'de' => 'Denkmäler',
                    'it' => 'Monumenti',
                    'ca' => 'Monuments',
                    'eu' => 'Monumentuak',
                    'gl' => 'Monumentos',
                    'nl' => 'Monumenten',
                ]
            ],
            [
                'name' => 'Museos',
                'icon' => 'WA.MUSEOS',
                'translation' => [
                    'es' => 'Museos',
                    'en' => 'Museums',
                    'fr' => 'Musées',
                    'pt' => 'Museus',
                    'de' => 'Museen',
                    'it' => 'Musei',
                    'ca' => 'Museus',
                    'eu' => 'Museoak',
                    'gl' => 'Museos',
                    'nl' => 'Musea',
                ]
            ],
            [
                'name' => 'Naturaleza',
                'icon' => 'WA.NATURALEZA',
                'translation' => [
                    'es' => 'Naturaleza',
                    'en' => 'Nature',
                    'fr' => 'Nature',
                    'pt' => 'Natureza',
                    'de' => 'Natur',
                    'it' => 'Natura',
                    'ca' => 'Natura',
                    'eu' => 'Natura',
                    'gl' => 'Natureza',
                    'nl' => 'Natuur',
                ]
            ],
            [
                'name' => 'Cafeterías y postres',
                'icon' => 'WA.COFFE',
                'translation' => [
                    'es' => 'Cafeterías y postres',
                    'en' => 'Cafes and desserts',
                    'fr' => 'Cafés et desserts',
                    'pt' => 'Cafeterias e sobremesas',
                    'de' => 'Cafés und Desserts',
                    'it' => 'Caffè e dessert',
                    'ca' => 'Cafeteries i postres',
                    'eu' => 'Kafetegiak eta postreak',
                    'gl' => 'Cafetarías e sobremesas',
                    'nl' => 'Cafés en desserts',
                ]
            ],
            [
                'name' => 'Restaurantes',
                'icon' => 'WA.RESTAURANTES',
                'translation' => [
                    'es' => 'Restaurantes',
                    'en' => 'Restaurants',
                    'fr' => 'Restaurants',
                    'pt' => 'Restaurantes',
                    'de' => 'Restaurants',
                    'it' => 'Ristoranti',
                    'ca' => 'Restaurants',
                    'eu' => 'Jatetxeak',
                    'gl' => 'Restaurantes',
                    'nl' => 'Restaurants',
                ]
            ],
            [
                'name' => 'Vida nocturna',
                'icon' => 'WA.VIDANOCTURNA',
                'translation' => [
                    'es' => 'Vida nocturna',
                    'en' => 'Nightlife',
                    'fr' => 'Vie nocturne',
                    'pt' => 'Vida noturna',
                    'de' => 'Nachtleben',
                    'it' => 'Vita notturna',
                    'ca' => 'Vida nocturna',
                    'eu' => 'Gau-bizitza',
                    'gl' => 'Vida nocturna',
                    'nl' => 'Nachtleven',
                ]
            ],
            [
                'name' => 'Compras',
                'icon' => 'WA.OCIO',
                'translation' => [
                    'es' => 'Compras',
                    'en' => 'Shopping',
                    'fr' => 'Achats',
                    'pt' => 'Compras',
                    'de' => 'Einkaufen',
                    'it' => 'Shopping',
                    'ca' => 'Compres',
                    'eu' => 'Erosketak',
                    'gl' => 'Compras',
                    'nl' => 'Winkelen',
                ]
            ],
            [
                'name' => 'Otros',
                'icon' => 'WA.OTROS',
                'translation' => [
                    'es' => 'Otros',
                    'en' => 'Others',
                    'fr' => 'Autres',
                    'pt' => 'Outros',
                    'de' => 'Andere',
                    'it' => 'Altri',
                    'ca' => 'Altres',
                    'eu' => 'Bestelakoak',
                    'gl' => 'Outros',
                    'nl' => 'Overige',
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
