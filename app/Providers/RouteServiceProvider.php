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
        Route::middleware(['api', 'setlocale', 'loadHotel', 'authStatic'])
             ->group(function () {
                 $this->loadModuleRoutes('api_chatgpt.php');
                 $this->loadModuleRoutes('api_hotel.php');
                 $this->loadModuleRoutes('api_hotel_ota.php');
                 $this->loadModuleRoutes('api_integration_pms.php');
                 $this->loadModuleRoutes('api_email.php');
                 $this->loadModuleRoutes('api_stay.php');
                 $this->loadModuleRoutes('api_guest.php');
                 $this->loadModuleRoutes('api_stay_survey.php');
                 $this->loadModuleRoutes('api_city.php');
                 $this->loadModuleRoutes('api_rewards.php');
                 $this->loadModuleRoutes('api_external_platforms.php');
                //  $this->loadModuleRoutes('api_experience.php');
                //  $this->loadModuleRoutes('api_place.php');
                 $this->loadModuleRoutes('api_chat.php');
                 $this->loadModuleRoutes('api_legal.php');
                 $this->loadModuleRoutes('api_emails.php');
                 $this->loadModuleRoutes('api_utils.php');
                 $this->loadModuleRoutes('api_facility.php');
                 $this->loadModuleRoutes('api_queries.php');
                 $this->loadModuleRoutes('api_stay_access.php');
                 $this->loadModuleRoutes('api_requests.php');
                 $this->loadModuleRoutes('api_data_services.php');
                 $this->loadModuleRoutes('api_tests.php');
                 $this->loadModuleRoutes('api_gallery.php');
                 $this->loadModuleRoutes('api_notifications.php');
                 $this->loadModuleRoutes('api_chain.php');
                 $this->loadModuleRoutes('api_dossier.php');
                 $this->loadModuleRoutes('api_hotel_buttons.php');
                 $this->loadModuleRoutes('api_metadata.php');
                 // Aquí puedes añadir más archivos de módulos según sea necesario
             });
    }

    protected function loadModuleRoutes(string $routeFile): void
    {
        Route::prefix('api')
             ->group(base_path('routes/' . $routeFile));
    }

}
