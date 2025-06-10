<?php

// app/Http/Middleware/SocialCrawlerMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Http\Controllers\MetaTagController;

class SocialCrawlerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $userAgent = strtolower($request->header('User-Agent'));
        $crawlers = ['facebookexternalhit', 'whatsapp', 'twitterbot', 'linkedinbot'];

        $isCrawler = false;
        foreach ($crawlers as $crawler) {
            if (str_contains($userAgent, $crawler)) {
                $isCrawler = true;
                break;
            }
        }

        if ($isCrawler) {
            try {
                $subdomain = $request->header('subdomainHotel');
                $hotel = Hotel::where('subdomain', $subdomain)->first();

                if (!$hotel) {
                    return $next($request);
                }

                // Obtener meta tags del controlador
                $controller = new MetaTagController();
                $response = $controller->getMetaTags($request);
                $metaData = $response->getData()->data;

                // Renderizar vista con meta tags
                return response()->view('social-meta', [
                    'title' => $metaData->title,
                    'description' => $metaData->description,
                    'image' => $metaData->image,
                    'url' => $metaData->url,
                    'type' => $metaData->type,
                    'site_name' => $metaData->site_name
                ]);
            } catch (\Exception $e) {
                return $next($request);
            }
        }

        return $next($request);
    }
}
