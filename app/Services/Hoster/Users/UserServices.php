<?php

namespace App\Services\Hoster\Users;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Mail\User\WelcomeUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Services\Hoster\Users\ProfileServices;


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
            return new UserResource($user);
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

    function getUserFilters2($filter)
    {
        $hotelIds = $this->getIdsHotels();
        /* $users = User::where(function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%')
                ->orWhereHas('workPosition', function ($subQuery) use ($filter) {
                    $subQuery->where('name', 'like', '%' . $filter['search_terms'] . '%');
                });
        }) */
        $users = User::where(function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%')
                ->orWhereHas('profile', function ($subQuery) use ($filter) {
                    $subQuery->orWhereHas('workPosition', function ($subQueryProfile) use ($filter) {
                        $subQueryProfile->where('name', 'like', '%' . $filter['search_terms'] . '%');
                    });
                });
        })
        ->whereHas('hotel', function ($query) use ($hotelIds) {
            $query->whereIn('hotel_id', $hotelIds);
        })
        /* ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Associate'); // Filter out users with the "Associate" role
        }) */

        ->when(isset($filter['type']), function ($query) use ($filter) {
            switch ($filter['type']) {
                case 0:
                    $query->where('del', 0);
                    break;
                case 1:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Associate');
                    });
                    break;
                case 2:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Administrator');
                    });
                    break;
                case 3:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Operator');
                    });
                    break;
                case 4:
                    $query->where('status', 0);
                    break;
                default:
                    $query->where('del', 0);
                    break;
            }
        })
        ->get()
        ->map(function ($user) {

            return $this->arrayMapUser($user);
        });

        return $users;
    }

    function getUserFiltersOLD($filter)
    {
        $hotelIds = $this->getIdsHotels();
        $query = User::myHotels($hotelIds);
        /* where(function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%');
        })
        ->orWhereHas('profile.workPosition', function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%');
        }); */

        $query->when(isset($filter['search_terms']) ? $filter['search_terms'] : null, function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%')
                ->orWhereHas('profile.workPosition', function ($subQuery) use ($filter) {
                    $subQuery->where('name', 'like', '%' . $filter['search_terms'] . '%');
                });
        })->orderBy('created_at', 'desc');



        $query->when(isset($filter['type']), function ($query) use ($filter, $hotelIds) {
            /* $query->whereHas('hotel', function ($query) use ($hotelIds) {
                $query->whereIn('hotel_id', $hotelIds);
            }); */
            switch ($filter['type']) {
                case 0:
                    break;
                case 1:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Associate');
                    });
                    break;
                case 2:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Administrator');
                    });
                    break;
                case 3:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Operator');
                    });
                    break;
                case 4:
                    $query->where('del', 1);
                    break;
                default:
                    $query->where('del', 0);
                    break;
            }
        });

        $perPage = $filter['per_page'] ?? 15; // Default 15
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

    function getUserFilters($filter)
    {
        $hotelIds = $this->getIdsHotels();
        $query = User::myHotels($hotelIds);

        $query->when(isset($filter['search_terms']) ? $filter['search_terms'] : null, function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%')
                ->orWhereHas('profile.workPosition', function ($subQuery) use ($filter) {
                    $subQuery->where('name', 'like', '%' . $filter['search_terms'] . '%');
                });
        })->orderBy('created_at', 'desc');


        $query->when(isset($filter['type']), function ($query) use ($filter, $hotelIds) {
            switch ($filter['type']) {
                case 0:
                    $query->where('del', 0);
                    break;
                case 1:
                    $query->where('status', 1)->where('del', 0);
                    break;
                /* case 2:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Administrator');
                    });
                    break;
                case 3:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Operator');
                    });
                    break; */
                case 4:
                    $query->where('status', 0);
                    break;
                default:
                    $query->where('del', 0);
                    break;
            }
        });

        $perPage = $filter['per_page'] ?? 15; // Default 15
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

    function getUserHotels2($perPage = 15, $page = 1)
    {
        $hotelIds = $this->getIdsHotels();

        $query = User::whereHas('hotel', function ($query) use ($hotelIds) {
            $query->whereIn('hotel_id', $hotelIds);
        })
        ->where('del', 0)
        ->orderBy('created_at', 'desc');

        $paginatedUsers = $query->paginate($perPage, ['*'], 'page', $page);

        return $paginatedUsers->map(function ($user) {
            return $this->arrayMapUser($user);
        });
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
        /* $users = User::whereHas('hotel', function ($query) use ($hotelIds) {
            $query->where('hotel_id', $this->get_first_hotel_permissions_exists());
        }) */
        /* ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Associate'); // Filter out users with the "Associate" role
        }) */
        ->where('del', 0)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($user) {

            return $this->arrayMapUser($user);
        });

        return $users;
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


    function getUsersHotelBasicData($hotelId)
    {
        $queryUsers = User::whereHas('hotel', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
        ->select('id', 'email', 'name')
        ->where('del', 0)
        ->orderBy('created_at', 'desc')
        ->get();

        if ($queryUsers->isEmpty()) {
            return [];
        }

        $users = $queryUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                //'role' => $user->getRoleName(),
            ];
        });

        return $users;
    }



    public function arrayMapUser($user)
    {
        if($user->profile?->phone == null){
            $phone = '';
            $prefix = '';
        }else{
            $phoneNumberParts = explode(' ', $user->profile->phone);

            $prefix = $phoneNumberParts[0];
            $phone = isset($phoneNumberParts[1]) ? $phoneNumberParts[1] : null;
        }

        //first hotel
        $firstHotel = $user->hotel->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'lastname' => $user->profile?->lastname ?? '--',
            'email' => $user->email,
            'del' => $user->del,
            'role' => 'user',  // rol
            'work_position' => $user->profile->work_position ?? $user->profile?->workPosition?->name,
            'work_position_id' => $user->profile?->work_position_id ?? null,
            'profile' => $user->profile ?? '--',
            'phone' => $phone,
            'prefix' => $prefix,
            'hotelsNameId' => $user->hotel->pluck('name', 'id'), // 'id' => 'name
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
            //'access' => $user->getAllPermissions()->pluck('name'),
            'firstHotelId' => $firstHotel->id ?? null,
            'time' => formatTimeDifference($user->created_at),
            'notifications' => json_decode($user->notifications),
            'periodicity_chat' => $user->periodicity_chat,
            'periodicity_stay' => $user->periodicity_stay,
            'permissions' => json_decode($user->permissions),
            'status' => $user->status,
            //'time' => $user->created_at->diffForHumans(),
        ];
    }


    public function getIdsHotels()
    {
        return auth()->user()->hotel->pluck('id');
    }

   /*  public function get_first_hotel_id()
    {
        if (auth()->user()->hotel->count() > 0) {
            return auth()->user()->hotel->first()->id;
        }else{
            return null;
        }
    }

    public function get_first_hotel_permissions_exists()
    {
        if(auth()->user()->hotel->count() == 1)
        {
            return auth()->user()->hotel->first()->id;
        }

        return auth()->user()->hotel->count() > 0
        ? (auth()->user()->hotel->first()->pivot->permissions
            ? auth()->user()->hotel->skip(1)->first()->id
            : auth()->user()->hotel->first()->id)
        : null;
    } */

    public function storeUserHoster($request)
    {
        $url = config('app.hoster_url');
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'parent_id' => $this->getParentId(),
            'password' => Hash::make($request->password),
            'permissions' => json_encode($request->permissions), // Guarda el JSON de permisos
            'notifications' => json_encode($request->notifications), // Guarda el JSON de notificaciones
            'periodicity_chat' => $request->periodicityChat,
            'periodicity_stay' => $request->periodicityStay,
        ]);

       /*  $role = $request->role == 1 ? 'Associate' : ($request->role == 2 ? 'Administrator' : 'Operator');

        $user->assignRole($role); */

        $this->profileServices->handleProfileHoster($request, $user);

        $this->storeHotelsUser($request, $user);

        Mail::to($user['email'])->send(new WelcomeUser($user,$url,$request->password,auth()->user()));

        return $user ?? false;
    }

    /* function getParentId() {
        $userRole = auth()->user()->getRoleNames()->first();

        switch ($userRole) {
            case 'Admin':
                return null;
            case 'Associate':
                return auth()->id();
            default:
                return auth()->user()->parent_id;
        }
    } */

    function getParentId() {
        $user = auth()->user();
        $userRole = $user->getRoleNames()->first();

        switch ($userRole) {
            case 'Admin':
                return $user->parent_id;
            case 'Associate':
                if ($user->owner != null) {
                    return $user->id;
                } else {
                    return $user->parent_id;
                }
            default:
                return $user->parent_id;
        }
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
            'periodicity_chat' => $request->periodicityChat,
            'periodicity_stay' => $request->periodicityStay,
        ]);

        /* if ($request->filled('role')) {
            $role = $request->role == 1 ? 'Associate' : ($request->role == 2 ? 'Administrator' : 'Operator');
            $user->syncRoles([$role]);
        } */

        $this->profileServices->handleProfileHoster($request, $user);

        $this->updateHotelsUser($request, $user);

        return $user;
    }

    public function updateHotelsUser($request, $user) {
        if ($request->hotels) {
            $user->hotel()->detach(); // borrar hoteles actuales
            foreach ($request->hotels as $key => $hotelId) {
                if (isset($request->access[$key]) && $request->access[$key] == true) {
                    $user->hotel()->attach($hotelId, ['permissions' => json_encode($request->access[$key])]);
                }
            }
        }
    }

    public function storeHotelsUser($request, $user) {
        if ($request->hotels) {
            foreach ($request->hotels as $key => $hotelId) {
                if (isset($request->access[$key]) && $request->access[$key] == true) {
                    $user->hotel()->attach($hotelId, ['permissions' => json_encode($request->access[$key])]);
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

}
