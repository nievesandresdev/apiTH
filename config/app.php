<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'pro'),
    'production' => env('PRODUCTION', 'false'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'passport_guest_client_id' => env('PASSPORT_CLIENT_ID_GUEST', null),
    'passport_guest_client_secret' => env('PASSPORT_CLIENT_SECRET_GUEST', null),

    'url' => env('APP_URL', 'http://localhost'),
    'guest_path' => env('LOCAL_GUEST_URL', 'http://localhost'),
    'mail_sender' => env('MAIL_FROM_ADDRESS', 'no-reply@thehster.io'),
    'url_bucket' => env('URL_BUCKET', 'https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test'),
    'storage_env' => env('AWS_STORAGE_PATH', null),
    'asset_url' => env('ASSET_URL'),
    'hoster_url' => env('HOSTER_URL'),
    'url_base_api_review' => env('URL_BASE_API_REVIEW',null),
    'url_base_helpers' => env('URL_BASE_API_HELPERS',null),
    'key_api_review' => env('KEY_API_REVIEW',null),
    'hotelId_dossier' => env('DOSSIER_HOTEL_ID',null),
    'mailgun_domain' => env('MAILGUN_DOMAIN', null),
    'mailgun_key' => env('MAILGUN_KEY', null),
    //OPENAI
    'openia_key' => env('OPENAI_API_KEY', null),
    'azure_openia_key' => env('AZURE_OPENAI_API_KEY', null),
    'azure_openia_base_uri' => env('AZURE_OPENAI_BASE_URI', null),
    'azure_openia_deployment' => env('AZURE_OPENAI_DEPLOYMENT', null),
    'azure_openia_version' => env('AZURE_OPENAI_VERSION', null),
    'discord_webhook_url' => env('DISCORD_WEBHOOK_URL', null),
    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */
    'timezone' => 'Europe/Madrid',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    //API VIATOR
    'key_viator' => env('KEY_VIATOR', ''),
    'viator' => env('API_VIATOR', 'https://api.sandbox.viator.com/partner'),

    'x_key_api' => env('X_KEY_API', null),

    /*n
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        Spatie\Permission\PermissionServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        // Intervention\Image\ImageServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        // 'Image' => Intervention\Image\Facades\Image::class
    ])->toArray(),

];