<?php

namespace App\Providers;

use App\Models\Stay;
use App\Models\StayAccess;
use App\Observers\StayAccessObserver;
use App\Observers\StayObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        StayAccess::observe(StayAccessObserver::class);
        Stay::observe(StayObserver::class);
        // $this->registerPolicies();
        // Passport::routes();
    }
}
