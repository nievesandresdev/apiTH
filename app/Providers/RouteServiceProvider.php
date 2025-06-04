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
                $path = app_path('Providers/Routes');
                foreach (glob($path . '/api_*.php') as $file) {
                    $this->loadModuleRoutes(basename($file));
                }
            });
    }

    protected function loadModuleRoutes(string $routeFile): void
    {
        Route::prefix('api')
            ->group(base_path('routes/' . $routeFile));
    }

}
