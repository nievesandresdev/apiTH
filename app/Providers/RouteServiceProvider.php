<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware(['api', 'setlocale', 'loadHotel'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            // Rutas modulares
            $this->loadApiRoutes();


            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });    
    }

    /**
        * Cargar rutas de API modulares.
    */

    protected function loadApiRoutes(): void
    {
        Route::middleware('api')
             ->group(function () {
                 $this->loadModuleRoutes('api_hotel.php');
                 $this->loadModuleRoutes('api_hotel_ota.php');
                 // Aquí puedes añadir más archivos de módulos según sea necesario
             });
    }

    /**
        * Cargar un archivo de rutas de módulo específico.
    *
        * @param string $routeFile Nombre del archivo de rutas.
    */
    protected function loadModuleRoutes(string $routeFile): void
    {
        Route::prefix('api')
             ->group(base_path('routes/' . $routeFile));
    }

}
