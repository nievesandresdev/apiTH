<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->header('Accept-Language');

        if ($locale) {
            // Extraer solo el primer idioma válido
            $localesValidos = ['es', 'en','fr', 'ca', 'eu', 'gl', 'nl', 'de', 'it', 'pt']; // Agrega más idiomas si es necesario
            $locale = strtok($locale, ','); // Toma solo la primera parte antes de la coma

            if (in_array($locale, $localesValidos)) {
                App::setLocale($locale);
            } else {
                App::setLocale(config('app.fallback_locale')); // Establece un fallback
            }
        }

        return $next($request);
    }
}
