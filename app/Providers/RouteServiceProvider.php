<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware(['api', 'setlocale', 'loadHotel', 'authStatic'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            // Rutas modulares
            $this->loadApiRoutes();


            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });    
    }

    protected function loadApiRoutes(): void
    {
        Route::middleware('api')
             ->group(function () {
                 $this->loadModuleRoutes('api_hotel.php');
                 $this->loadModuleRoutes('api_hotel_ota.php');
                 $this->loadModuleRoutes('api_stay.php');
                 $this->loadModuleRoutes('api_guest.php');
                 $this->loadModuleRoutes('api_stay_survey.php');
                 $this->loadModuleRoutes('api_city.php');
                 $this->loadModuleRoutes('api_experience.php');
                 $this->loadModuleRoutes('api_place.php');
                 $this->loadModuleRoutes('api_chat.php');
                 $this->loadModuleRoutes('api_utils.php');
                 $this->loadModuleRoutes('api_facility.php');
                 // Aquí puedes añadir más archivos de módulos según sea necesario
             });
    }

    protected function loadModuleRoutes(string $routeFile): void
    {
        Route::prefix('api')
             ->group(base_path('routes/' . $routeFile));
    }

}
