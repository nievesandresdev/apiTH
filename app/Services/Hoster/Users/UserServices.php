<?php

namespace App\Services\Hoster\Users;

use App\Models\User;
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
            'selected_hotel' => $this->get_first_hotel_permissions_exists(),
            'search_terms' => null,
            'type' => 0,
        ];
    }

    public function getUsersBasedOnRequest(&$data_filter)
    {
        $users = $this->get_user_hotels_map();

        if (request()->hotel) {
            $data_filter['selected_hotel'] = request()->hotel;
            $users = $this->get_user_get_hotel_by_id(request()->hotel);
        }

        if (request()->search_terms) {
            $users = $this->filterUsersBySearchTerms($data_filter);
        }

        if (request()->type) {
            $users = $this->filterUsersByType($data_filter);
        }

        return $users;
    }

    public function filterUsersBySearchTerms(&$data_filter)
    {
        $data_filter['search_terms'] = request()->search_terms;
        return $this->get_user_by_filters(request()->all());
    }

    public function filterUsersByType(&$data_filter)
    {
        $data_filter['type'] = request()->type;
        return $this->get_user_by_filters(request()->all());
    }


    function get_user_hotels_map()
    {
        $hotelIds = $this->get_ids_hotels();
        /* dd($hotelIds); */

        /* $users = User::whereHas('hotel', function ($query) use ($hotelIds) {
            $query->whereIn('hotel_id', $hotelIds);
        }) */
        $users = User::whereHas('hotel', function ($query) use ($hotelIds) {
            $query->where('hotel_id', $this->get_first_hotel_permissions_exists());
        })
        /* ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Associate'); // Filter out users with the "Associate" role
        }) */
        ->where('del', 0)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($user) {

            return $this->array_map_user($user);
        });

        return $users;
    }

    function get_user_get_hotel_by_id($id)
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

            return $this->array_map_user($user);
        });

        return $users;
    }

    function get_user_by_filters($filter)
    {
        $users = User::where(function ($query) use ($filter) {
            $query->where('name', 'like', '%' . $filter['search_terms'] . '%')
                ->orWhereHas('profile', function ($subQuery) use ($filter) {
                    $subQuery->where('work_position', 'like', '%' . $filter['search_terms'] . '%');
                });
        })
        ->whereHas('hotel', function ($query) use ($filter) {
            $query->where('hotel_id', $filter['hotel']);
        })
        ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Associate'); // Filter out users with the "Associate" role
        })
        ->where('del', 0)
        ->when(isset($filter['type']), function ($query) use ($filter) {
            switch ($filter['type']) {
                case 0:
                    break;
                case 1:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Administrator');
                    });
                    break;
                case 2:
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'Operator');
                    });
                    break;
                case 3:
                    $query->where('del', 1);
                    break;
            }
        })
        ->get()
        ->map(function ($user) {

            return $this->array_map_user($user);
        });

        return $users;
    }

    public function array_map_user($user)
    {
        $phoneNumberParts = explode(' ', $user->profile->phone);

        $prefix = $phoneNumberParts[0];
        $phone = isset($phoneNumberParts[1]) ? $phoneNumberParts[1] : null;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRole(),  // rol
            'work_position' => $user->profile->work_position ?? '--',
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
            'access' => $user->getAllPermissions()->pluck('name'),
        ];
    }


    public function get_ids_hotels()
    {
        return auth()->user()->hotel->pluck('id');
    }

    public function get_first_hotel_id()
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

        /* if (auth()->user()->hotel->count() ) {
            return auth()->user()->hotel->first()->pivot->permissions
                ? auth()->user()->hotel->skip(1)->first()->id
                : auth()->user()->hotel->first()->id;
        } */

        return auth()->user()->hotel->count() > 0
        ? (auth()->user()->hotel->first()->pivot->permissions
            ? auth()->user()->hotel->skip(1)->first()->id
            : auth()->user()->hotel->first()->id)
        : null;
    }

    public function store_user_hoster($request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'parent_id' => $this->getParentId(),
            'password' => Hash::make($request->password),
        ]);

        $role = $request->role == 1 ? 'Administrator' : 'Operator';

        $user->assignRole($role);

        $this->profileServices->handle_profile_hoster($request, $user);

       $this->storeHotelsUser($request, $user);

      //$this->storeAccessUser($request, $user);


        /* if($user){
            return $user;
        }else{
            return false;
        } */

        return $user ?? false;
    }

    function getParentId() {
        $userRole = auth()->user()->getRoleNames()->first();

        switch ($userRole) {
            case 'Admin':
                return null;
            case 'Associate':
                return auth()->id();
            default:
                return auth()->user()->parent_id;
        }
    }


    public function storeHotelsUser($request, $user) {
        if ($request->hotels) {
            foreach ($request->hotels as $key => $hotelId) {
                if (isset($request->hotelPermissions[$key]) && $request->hotelPermissions[$key] == true) {
                    $user->hotel()->attach($hotelId, ['permissions' => json_encode($request->hotelPermissions[$key])]);
                }
            }
        }
    }

    public function storeAccessUser($request, $user){
        if($request->access){
            foreach($request->access as $access){
                $user->givePermissionTo($access);
            }
        }
    }

    public function update_user_hoster($request, $userId)
    {
        $user = User::findOrFail($userId);


        $request->validate([
            'email' => 'required|email|unique:users,email,' . $userId,
            'name' => 'required|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('role')) {
            $role = $request->role == 1 ? 'Administrator' : 'Operator';
            $user->syncRoles([$role]);
        }

        $this->profileServices->handle_profile_hoster($request, $user);

        $this->updateHotelsUser($request, $user);

        //$this->updateAccessUser($request, $user);

        return $user;
    }

    public function updateHotelsUser($request, $user) {
        if ($request->filled('hotels')) {
            $user->hotel()->detach(); // borrar hoteles actuales
            foreach ($request->hotels as $key => $hotelId) {
                if (isset($request->hotelPermissions[$key]) && $request->hotelPermissions[$key] == true) {
                    $user->hotel()->attach($hotelId, ['permissions' => json_encode($request->hotelPermissions[$key])]);
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

    public function delete_user_hoster($userId)
    {
        $user = User::findOrFail($userId);

        $user->del = 1;
        $user->save();

        return $user;
    }

}
