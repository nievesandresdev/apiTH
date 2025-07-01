<?php

namespace App\Providers;

use App\Models\Stay;
use App\Models\StayAccess;
use App\Observers\StayAccessObserver;
use App\Observers\StayObserver;
use Carbon\Carbon;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
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
        Event::listen(MessageSending::class, function (MessageSending $event) {
            // Log::info('MessageSending emtro aqui');
            $exclusiones = ['@email'];
    
            // Para Laravel 9/10: $event->message es un Symfony\Mime\Email.
            $destinatarios = collect($event->message->getTo())
                ->pluck('address');   // ->getAddress() en versiones < 9
    
            if ($destinatarios->contains(
                    fn ($addr) => Str::contains($addr, $exclusiones)
                )) {
                return false;        // aborta el envío
            }
        });
    }
}
