<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Users\UserServices;
use App\Utils\Enums\EnumResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    protected $userServices;

    public function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }

    public function getUsers()
    {
        $data_filter = $this->userServices->initializeDataFilter();

        $data_filter['per_page'] = request()->get('per_page', 15); // default 15
        $data_filter['page'] = request()->get('page', 1); // default 1

        $users = $this->userServices->getUsersBasedOnRequest($data_filter);

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'users' => $users->items(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            //'test' => $this->userServices->get_ids_hotels()
        ]);
    }

    public function store()
    {
        try {
           request()->validate([
                'email' => 'required|email|unique:users',
            ],
            [
                'email.required' => 'El campo email es requerido',
                'email.email' => 'El campo email debe ser un correo electrónico válido',
                'email.unique' => 'El correo electrónico ya está en uso',
            ]);

            // Si la validación pasa, proceder a crear el usuario
            $user = $this->userServices->storeUserHoster(request());

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario creado con éxito',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }

    public function update()
    {
        try {
            request()->validate([
                'email' => 'required|email|unique:users,email,' . request()->user_id,
            ],
            [
                'email.required' => 'El campo email es requerido',
                'email.email' => 'El campo email debe ser un correo electrónico válido',
                'email.unique' => 'El correo electrónico ya está en uso',
            ]);

            $user = $this->userServices->updateUserHoster(request(), request()->user_id);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario actualizado con éxito',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }

    public function updateProfile()
    {
        try {
            $user = $this->userServices->updateProfileHoster(request(), request()->user_id);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Perfil actualizado con éxito',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }
}
