<?php

namespace App\Services\Hoster\Users;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Mail\User\WelcomeUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Services\Hoster\Users\ProfileServices;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class UserServices
{
    protected $profileServices;

    public function __construct(ProfileServices $profileServices)
    {
        $this->profileServices = $profileServices;
    }

    //funciones principales
    public function initializeDataFilter()
    {
        return [
            'selected_hotel' => $this->getIdsHotels(),
            'search_terms' => null,
            'type' => 0,
        ];
    }

    public function getUsersBasedOnRequest(&$data_filter)
    {
        $perPage = request()->get('per_page', 20);
        $page = request()->get('page', 1);

        $users = $this->getUserHotels($perPage, $page);

        if (request()->search_terms) {
            $users = $this->filterUsersBySearchTerms($data_filter);
        }

        if (request()->type) {
            $users = $this->filterUsersByType($data_filter);
        }

        return $users;
    }

    public function getUserById($id)
    {
        $user = User::findOrFail($id);

        if ($user) {
            return $this->arrayMapUser($user);
        }else{
            return false;
        }
    }

    public function filterUsersBySearchTerms(&$data_filter)
    {
        $data_filter['search_terms'] = request()->search_terms;
        return $this->getUserFilters(request()->all());
    }

    public function filterUsersByType(&$data_filter)
    {
        $data_filter['type'] = request()->type;
        return $this->getUserFilters(request()->all());
    }


    function getUserFilters($filter)
    {
        $hotelIds = $this->getIdsHotels();
        // Excluir siempre los usuarios eliminados (del = 1)
        $query = User::myHotels($hotelIds)->where('del', 0);

        $query->when(isset($filter['search_terms']) ? $filter['search_terms'] : null, function ($query) use ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search_terms'] . '%')
                  ->orWhereHas('profile.workPosition', function ($subQuery) use ($filter) {
                      $subQuery->where('name', 'like', '%' . $filter['search_terms'] . '%');
                  });
            });
        })->orderBy('created_at', 'desc');


        $query->when(isset($filter['type']), function ($query) use ($filter) {
            switch ($filter['type']) {
                case 1:
                    // Solo usuarios activos
                    $query->where('status', 1);
                    break;
                case 4:
                    // Solo usuarios inactivos
                    $query->where('status', 0);
                    break;
                default:
                    // No es necesario incluir aquí el filtro del = 0 porque ya está al inicio
                    break;
            }
        });

        $perPage = $filter['per_page'] ?? 15; // Por defecto 15
        $page = $filter['page'] ?? 1;

        $paginatedUsers = $query->paginate($perPage, ['*'], 'page', $page);

        // Mapeo de usuarios
        $mappedUsers = $paginatedUsers->getCollection()->map(function ($user) {
            return $this->arrayMapUser($user);
        });

        // Crear una nueva instancia de LengthAwarePaginator con los datos mapeados
        $paginatedUsers->setCollection($mappedUsers);

        return $paginatedUsers;
    }


    function getUserHotels($perPage = 15, $page = 1)
    {
        $hotelIds = $this->getIdsHotels();

        $query = User::whereHas('hotel', function ($query) use ($hotelIds) {
            $query->whereIn('hotel_id', $hotelIds);
        })
        ->where('del', 0)
        ->orderBy('created_at', 'desc');

        $paginatedUsers = $query->paginate($perPage, ['*'], 'page', $page);

        $mappedUsers = $paginatedUsers->getCollection()->map(function ($user) {
            return $this->arrayMapUser($user);
        });

        // Crear una nueva instancia de LengthAwarePaginator con los datos mapeados
        $paginatedUsers->setCollection($mappedUsers);

        return $paginatedUsers;
    }


    function getUserHotelsOriginal()
    {
        $hotelIds = $this->getIdsHotels();
        /* dd($hotelIds); */

        $users = User::whereHas('hotel', function ($query) use ($hotelIds) {
            $query->whereIn('hotel_id', $hotelIds);
        })

        ->where('del', 0)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($user) {

            return $this->arrayMapUser($user);
        });

        return $users;
    }

    function getTrial() {
        $user = auth()->user();

        // Si el parent_id es null, usamos los datos del propio usuario
        $trialDuration = $user->parent?->trial_duration_current ?? $user->trial_duration_current ?? null;
        $trialEndsAt = $user->parent?->trial_ends_at ?? $user->trial_ends_at ?? null;
        $trialStartsAt = $user->parent?->trial_starts_at ?? $user->trial_starts_at ?? null;

        // si el trial está activo (entre trial_starts_at y trial_ends_at)
        $isTrialActive = $trialStartsAt && $trialStartsAt->isPast() && $trialEndsAt && $trialEndsAt->isFuture();

        // si el trial está vencido (ya ha comenzado pero el trial_ends_at es en el pasado)
        $isTrialExpired = $trialEndsAt && !$trialEndsAt->isFuture();

        // Calcular la duración real del trial en días
        $trialDurationInDays = null;
        $trialDurationFormatted = null;
        if ($trialStartsAt && $trialEndsAt) {
            $trialDurationInDays = $trialStartsAt->diffInDays($trialEndsAt);
            $trialDurationFormatted = $trialDurationInDays === 1 ? "$trialDurationInDays día" : "$trialDurationInDays días";
        }

        // Devolver un array con los datos
        return [
            'trial_duration_current' => $trialDuration,
            'trial_starts_at' => $trialStartsAt,
            'trial_ends_at' => $trialEndsAt,
            'is_active' => $isTrialActive,
            'is_expired' => $isTrialExpired,
            'starts_at_formatted' => $trialStartsAt ? $trialStartsAt->format('Y-m-d H:i:s') : null,
            'ends_at_formatted' => $trialEndsAt ? $trialEndsAt->format('Y-m-d H:i:s') : null,
            'trial_duration_calculated' => $trialDurationInDays,
            'trial_duration_formatted' => $trialDurationFormatted,
        ];
    }







    function getUserHotelById($id)
    {

        $users = User::whereHas('hotel', function ($query) use ($id) {
            $query->where('hotel_id', $id);
        })
        ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Associate'); // Filter out users with the "Associate" role
        })
        ->where('del', 0)
        ->get()
        ->map(function ($user) {

            return $this->arrayMapUser($user);
        });

        return $users;
    }



    function getUsersHotelBasicData($hotelId, $notificationFilters = [], $specificChannels = [])
    {
        // Validar que $specificChannels sea un array
        if (!is_array($specificChannels)) {
            $specificChannels = [];
        }

        $queryUsers = User::whereHas('hotel', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->select('id', 'email', 'name', 'notifications')
            ->whereNotNull('notifications')
            ->where('del', 0)
            ->where('status', 1);

        //Log::info('getUsersHotelBasicData $queryUsers'. json_encode($queryUsers->get()));

        // Validar si se pasaron filtros de notificación
        if (!empty($notificationFilters)) {
            foreach ($notificationFilters as $key => $value) {
                //Log::info('getUsersHotelBasicData $key '. $key);
                $queryUsers->where(function ($query) use ($key, $value, $specificChannels) {
                    //Log::info('getUsersHotelBasicData $specificChannels '. json_encode($specificChannels));
                    foreach ($specificChannels as $channel) {
                        //Log::info('getUsersHotelBasicData $channelxX '. $channel);
                        $query->orWhere("notifications->{$channel}->$key", $value);
                    }
                });
            }
        }

        $queryUsers = $queryUsers->orderBy('created_at', 'desc')->get();

        //Log::info('getUsersHotelBasicData $queryUsers '. json_encode($queryUsers));

        if ($queryUsers->isEmpty()) {
            // Retorna un array con colecciones vacías para cada canal
            return collect(array_fill_keys($specificChannels, collect()));
        }

        //Log::info('getUsersHotelBasicData $specificChannelsxXXxxX: ' . json_encode($specificChannels, JSON_PRETTY_PRINT));



        // Separar los resultados en grupos dinámicos según $specificChannels
        $groupedUsers = [];
        foreach ($specificChannels as $channel) {
            //Log::info('getUsersHotelBasicData $channelxX '. $channel);
            $groupedUsers[$channel] = collect($queryUsers);
        }

        //Log::info('getUsersHotelBasicData $groupedUsersxXX ' . json_encode($groupedUsers, JSON_PRETTY_PRINT));

        $queryUsers->each(function ($user) use (&$groupedUsers, $specificChannels, $notificationFilters) {
            $notifications = $user->notifications;

            foreach ($notificationFilters as $key => $value) {
                foreach ($specificChannels as $channel) {
                    if (($notifications[$channel][$key] ?? false) === $value) {
                        $groupedUsers[$channel]->push($user);
                    }
                }
            }
        });

        //Log::info('getUsersHotelBasicData $groupedUsers '. json_encode($groupedUsers));

        return collect($groupedUsers);
    }

    /**
     * Get users with specific notification settings for authenticated user's hotels
     */
    function getUsersWithNotifications($notificationFilters = [], $specificChannels = [], $periodicity = null)
    {
        // Validar que $specificChannels sea un array
        if (!is_array($specificChannels)) {
            $specificChannels = [];
        }

        $queryUsers = User::select('id', 'email', 'name', 'notifications','login_code')
            ->with(['hotel:id,name,created_at'])
            ->whereNotNull('notifications')
            ->where('del', 0)
            ->where('status', 1)
            ->whereHas('hotel');

        // Validar si se pasaron filtros de notificación
        if (!empty($notificationFilters)) {
            foreach ($notificationFilters as $key => $value) {
                $queryUsers->where(function ($query) use ($key, $value, $specificChannels) {
                    foreach ($specificChannels as $channel) {
                        $query->orWhere("notifications->{$channel}->$key", $value);
                    }
                });
            }
        }

        // Filtrar por periodicity si se especifica
        if ($periodicity !== null) {
            $queryUsers->whereRaw("JSON_EXTRACT(notifications, '$.informGeneral.periodicity') = ?", [$periodicity]);
        }

        $queryUsers = $queryUsers->orderBy('created_at', 'desc')->get();

        Log::info('Número de usuarios encontrados: ' . $queryUsers->count());

        if ($queryUsers->isEmpty()) {
            Log::info('No se encontraron usuarios');
            return collect(array_fill_keys($specificChannels, collect()));
        }

        // Separar los resultados en grupos dinámicos según $specificChannels
        $groupedUsers = [];
        foreach ($specificChannels as $channel) {
            $groupedUsers[$channel] = collect();
        }

        // Debug de los usuarios encontrados
        foreach ($queryUsers as $user) {

            // Intentar decodificar las notificaciones
            $notifications = is_string($user->notifications) ? json_decode($user->notifications, true) : $user->notifications;

            // Verificar la estructura de las notificaciones
            foreach ($notificationFilters as $key => $value) {
                foreach ($specificChannels as $channel) {


                    if (($notifications[$channel][$key] ?? false) === $value) {
                        $groupedUsers[$channel]->push($user);
                        //Log::info("Usuario {$user->email} agregado al canal {$channel}");
                    }
                }
            }
        }


        return collect($groupedUsers);
    }

    public function arrayMapUser($user)
    {

        //first hotel
        $firstHotel = $user->hotel->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'lastname' => $user->profile?->lastname ?? '',
            'email' => $user->email,
            'del' => $user->del,
            'role' => 'user',
            'work_position' => $user->profile?->workPosition,
            'work_position_id' => $user->profile?->work_position_id ?? null,
            'profile' => $user->profile ?? '--',
            'phone' => $user->profile->phone,
            'prefix' => null,
            'hotelsNameId' => $user->hotel->pluck('name', 'id'),
            'hotels' => $user->hotel->pluck('id'),
            'hotelsData' => $user->hotel->map(function ($hotel) {
                return [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'permissions' => json_decode($hotel->pivot->permissions),
                ];
            }),
            'hotelPermissions' => $user->hotel->map(function ($hotel) {
                return json_decode($hotel->pivot->permissions);

            }),
            //'accessUser' => $user->permissions,
            //'access' => $user->getAllPermissions()->pluck('name'),
            'firstHotelId' => $firstHotel->id ?? null,
            'time' => formatTimeDifference($user->created_at),
            'notifications' => $user->profile?->work_position_id ? json_decode($user->profile?->workPosition->notifications) : json_decode($user->notifications),
            'periodicity_chat' => $user->profile?->work_position_id ? json_decode($user->profile?->workPosition->periodicity_chat) : json_decode($user->periodicity_chat),
            'periodicity_stay' => $user->profile?->work_position_id ? json_decode($user->profile?->workPosition->periodicity_stay) : json_decode($user->periodicity_stay),
            'permissions' => $user->permissions,
            'status' => $user->status,
            'owner' => $user->owner
            //'time' => $user->created_at->diffForHumans(),
        ];
    }


    public function getIdsHotels()
    {
        return auth()->user()->hotel->pluck('id');
    }

    public function store($request)
    {
        $url = config('app.hoster_url');
        $user = User::create([
            'name' => $request->name,
            'type_user' => $request->type_user ?? 'admin',             
            'email' => $request->email,
            'parent_id' => $this->getParentId(),
            'password' => Hash::make($request->password),
            'permissions' => json_encode($request->permissions), // Guarda el JSON de permisos
            'notifications' => json_encode($request->notifications),
            'periodicity_chat' => json_encode($request->periodicityChat),
            'periodicity_stay' => json_encode($request->periodicityStay),
        ]);

        return $user;
    }


    public function storeUserHoster($request)
    {
        $url = config('app.hoster_url');
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'parent_id' => null,
            'password' => Hash::make($request->password),
            'permissions' => $request->permissions ? json_encode($request->permissions) : null, // Guarda el JSON de permisos
            'notifications' => $request->notifications ? json_encode($request->notifications) : null,
            'periodicity_chat' => $request->periodicityChat ? json_encode($request->periodicityChat) : null,
            'periodicity_stay' => $request->periodicityStay ? json_encode($request->periodicityStay) : null,
            'type_user' => $request->type_user ?? 'admin',
        ]);



        $this->profileServices->handleProfileHoster($request, $user);

        $this->storeHotelsUser($request, $user);

        Mail::to($user['email'])->send(new WelcomeUser($user,$url,$request->password,auth()->user()));

        return $user ?? false;
    }



    function getParentId() {
        $user = auth()->user();

        return $user->parent_id ?? $user->id;


    }


    function getUserId() {
        return User::findOrFail(auth()->id());
    }

    public function storeAccessUser($request, $user){
        if($request->access){
            foreach($request->access as $access){
                $user->givePermissionTo($access);
            }
        }
    }

    public function updateUserHoster($request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $userId,
            'name' => 'required|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'permissions' => json_encode($request->permissions), // Guarda el JSON de permisos
            'notifications' => json_encode($request->notifications), // Guarda el JSON de notificaciones
            'periodicity_chat' => json_encode($request->periodicityChat),
            'periodicity_stay' => json_encode($request->periodicityStay),
        ]);



        $this->profileServices->handleProfileHoster($request, $user);

        $this->updateHotelsUser($request, $user);

        return $user;
    }

    public function updateHotelsUser($request, $user) {
        if ($request->hotels) {
            $user->hotel()->detach(); // borrar hoteles actuales
            foreach ($request->hotels as $key => $hotelId) {
                //if (isset($request->access[$key]) && $request->access[$key] == true) {
                    $user->hotel()->attach($hotelId, ['permissions' => json_encode($request->permissions)]);
                //}
            }
        }
    }



    public function storeHotelsUser($request, $user) {
        if ($request->hotels) {
            // Verifica cuántos hoteles se están agregando
            $totalHotels = count($request->hotels);

            foreach ($request->hotels as $key => $hotelId) {
                if (isset($request->access[$key]) && $request->access[$key] == true) {
                    $isDefault = ($totalHotels == 1) ? 1 : 0;

                    $user->hotel()->attach($hotelId, [
                        'permissions' => json_encode($request->access[$key]),
                        'is_default' => $isDefault
                    ]);
                }
            }
        }
    }

    public function updateAccessUser($request, $user) {
        if($request->filled('access')){
            $user->permissions()->detach(); // borra todos los permisos actuale
            foreach($request->access as $access){
                $user->givePermissionTo($access);
            }
        }
    }


    public function deleteUserHoster($userId)
    {
        $user = User::findOrFail($userId);

        $user->del = 1;
        $user->email = $user->email.$userId;
        $user->save();

        return $user;
    }

    public function disabledUserHoster($userId)
    {
        $user = User::findOrFail($userId);

        $user->status = 0;
        $user->save();

        return $user;
    }

    public function enabledUserHoster($userId)
    {
        $user = User::findOrFail($userId);

        $user->status = 1;
        $user->save();

        return $user;
    }

    public function checkCurrentPassword($request)
    {
        $user = User::where('email', $request->email)->first();

        // Comparar la nueva contraseña con la actual
        if (Hash::check($request->password, $user->password)) {
            return ['valid' => false, 'message' => 'La nueva contraseña no puede ser igual a la actual'];
        }

        return ['valid' => true, 'message' => 'Contraseña actualizada correctamente'];
    }



}
