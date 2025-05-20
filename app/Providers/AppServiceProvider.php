<?php

namespace App\Providers;

use App\Models\Stay;
use App\Models\StayAccess;
use App\Observers\StayAccessObserver;
use App\Observers\StayObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;


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
        Carbon::setLocale('es');
        
        StayAccess::observe(StayAccessObserver::class);
        Stay::observe(StayObserver::class);
    }
}
