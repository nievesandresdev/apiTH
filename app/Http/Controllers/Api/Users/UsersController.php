<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Users\{UserServices, ProfileServices};
use App\Services\Apis\ApiReviewServices;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use Illuminate\Support\Facades\Hash;
use App\Utils\Enums\EnumResponse;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Services\MailService;
use App\Mail\User\WelcomeUser;
use App\Mail\Queries\NewFeedback;
use App\Models\Guest;
use App\Models\Stay;
use App\Models\Query;
use App\Models\User;
use App\Mail\Chats\ChatEmail;
use Illuminate\Support\Facades\Mail;


class UsersController extends Controller
{
    protected $userServices;
    protected $profileServices;
    protected $mailService;
    protected $serviceQuery;
    protected $settings;
    protected $api_review_service;


    public function __construct(
        UserServices $userServices,
        ProfileServices $profileServices,
        MailService $_MailService,
        QueryServices $serviceQuery,
        QuerySettingsServices $_QuerySettingsServices,
        ApiReviewServices $_api_review_service
    )
    {
        $this->userServices = $userServices;
        $this->profileServices = $profileServices;
        $this->mailService = $_MailService;
        $this->serviceQuery = $serviceQuery;
        $this->settings = $_QuerySettingsServices;
        $this->api_review_service = $_api_review_service;
    }

    public function getUsers()
    {
        $data_filter = $this->userServices->initializeDataFilter();

        $data_filter['per_page'] = request()->get('per_page', 20); // default 15
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

    public function getUser()
    {
        $user = $this->userServices->getUserById(auth()->id());

        if (!$user) {
            return bodyResponseRequest(EnumResponse::NOT_FOUND, [
                'message' => 'Usuario no encontrado',
            ]);
        }else{
            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'user' => $user
            ]);
        }
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

            /* return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario creado con éxito',
                'user' => request()->all()
            ]); */

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
                'email' => 'required|email|unique:users,email,' .request()->user_id,
            ],
            [
                'email.required' => 'El campo email es requerido',
                'email.email' => 'El campo email debe ser un correo electrónico válido',
                'email.unique' => 'El correo electrónico ya está en uso',
            ]);

            /* return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario actualizado con éxito',
                'user' => request()->all()
            ]); */

            $user = $this->userServices->updateUserHoster(request(),request()->user_id);

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

    public function updateProfile(Request $request)
    {
        try {
            $userId = auth()->id();

            $request->validate([
                'name' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $userId,
                'prefix' => 'required|string|max:5',
                'phone' => 'required|string|max:15',
                //'current_password' => 'required_with:new_password|string|min:6',
            ]);

            $user = User::findOrFail($userId);

            // Validar la contraseña actual
            if ($request->filled('new_password') && !Hash::check($request->current_password, $user->password)) {

                return bodyResponseRequest(EnumResponse::ERROR, [],null,'La contraseña actual no es correcta');
            }

            // Actualizar los datos del perfil
            $user->name = $request->name;
            $user->email = $request->email;

            $this->profileServices->handleUpdateProfileHoster($request, $user);

            // Actualizar la contraseña
            if ($request->filled('new_password')) {
                $user->password = bcrypt($request->new_password);
            }

            $user->save();

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Actualizado Correctamente',
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ], null, $e->getMessage());
        }
    }

    public function delete(){
        try {
            $user = $this->userServices->deleteUserHoster(request()->user_id);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario eliminado con éxito',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }

    public function disabled(){
        try {
            $user = $this->userServices->disabledUserHoster(request()->user_id);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario deshabilitado con éxito',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }

    public function enabled(){
        try {
            $user = $this->userServices->enabledUserHoster(request()->user_id);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Usuario habilitado con éxito',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }



    public function getStatusSubscription(Request $request){
        try{
            $user = $this->userServices->getUserId();
            $hotel = $request->attributes->get('hotel');

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'status' => $user->status_subscription($hotel),
            ]);
        }catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }


    public function testMail(Request $request){
        try {
            $url = config('app.hoster_url');
            $hotel = $request->attributes->get('hotel');
            $query = Query::find(245);
            $guest = Guest::select('id','phone','email','name')->where('id',171)->first();
            $stay = Stay::find(46);
            $periodUrl = $query->period_id;
            $urlQuery = config('app.hoster_url')."tablero-hoster/estancias/consultas/".$periodUrl."?selected=".$stay->id;
            //url para atender chat $url/estancias/{stayId}/chat?g=guestId
            $user = User::findOrFail(1);

            $checkinFormat = date('d/m/Y', strtotime($stay->check_in));
            $checkoutFormat = date('d/m/Y', strtotime($stay->check_out));

            $dates = "$checkinFormat - $checkoutFormat";
            //$this->mailService->sendEmail(new ChatEmail('sss'), "francisco20990@gmail.com");
            $this->mailService->sendEmail(new WelcomeUser($user,$url,'12345',auth()->user()), "francisco20990@gmail.com");
            //$this->mailService->sendEmail(new ChatEmail([],$url,'new'), 'francisco20990@gmail.com');
            //Mail::to('francisco20990@gmail.com')->send(new NewFeedback($dates, $urlQuery, $hotel ,$query,$guest,$stay, 'new'));

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Correo enviado con éxito',
                'data' => [
                    'query' => $query,
                    'guest' => $guest,
                    'stay' => $stay,
                    'hotel' => $hotel,
                    'url' => $urlQuery,
                ]
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => $e->getMessage(),
            ],null,$e->getMessage());
        }
    }
}
