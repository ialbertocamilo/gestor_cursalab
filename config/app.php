<?php

use Illuminate\Support\Facades\Facade;

return [
    'CHATBOT_HUBSPOT' => env('CHATBOT_HUBSPOT',''),
    'gtag'=>[
        'gestor'=> env('GTAG_GESTOR',NULL)
    ],

    'test_environment' => env('TEST_ENVIRONMENT', false),
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
    'customer' => [
        'id' => env('CUSTOMER_ID', 'cursalab-id'),
        'slug' => env('CUSTOMER_SLUG', 'cursalab-default'),
    ],

    // App versions

    'versions' => [
        'ios' => env('APP_VERSION_IOS'),
        'android' => env('APP_VERSION_ANDROID'),
    ],

    'notifications' => [
        'showModalM1' => env('SHOW_MODAL_M1'),
        'showCloseButtonM1' => env('SHOW_CLOSE_BUTTON_M1'),
        'showModalM2' => env('SHOW_MODAL_M2'),
        'showCloseButtonM2' => env('SHOW_CLOSE_BUTTON_M2'),
        'showModalM3' => env('SHOW_MODAL_M3'),
        'showCloseButtonM3' => env('SHOW_CLOSE_BUTTON_M3'),
        'showMessageM4' => env('SHOW_MESSAGE_M4'),
        'showIosLink' => env('SHOW_IOS_LINK'),
    ],

    'meetings' => [
        'app_upload_template' => 'http://sfo2.digitaloceanspaces.com/cursalab2-statics/cursalab-assets/Plantilla-Importar-Invitados.xlsx',
    ],

    // Evaluation Quiz Config

    'quizzes' => [
        'duration' => env('QUIZZES_DURATION', 1), // in hours
    ],

    // Impersonation APP

    'impersonation' => [
        'enabled' => env('APP_IMPERSONATION_ENABLED', false),
        'link_duration' => env('APP_IMPERSONATION_LINK_DURATION_SECONDS', 45),
        'code' => env('APP_IMPERSONATION_CODE'),
        'fields' => [
            ['name' => 'username', 'label' => 'Usuario' , 'hidden' => false, 'required' => true],
            ['name' => 'password', 'label' => 'Contraseña' , 'hidden' => true, 'required' => true],
            ['name' => 'document', 'label' => 'Documento' , 'hidden' => true, 'required' => true],
            ['name' => 'code', 'label' => 'Código' , 'hidden' => true, 'required' => true],
        ],
        'button' => [
            'hidden' => false,
            'label' => 'Enviar',
        ]
    ],

    // Password configuration

    'passwords' => [
        'app' => [
            'expiration_days' => env('RESET_PASSWORD_DAYS_APP', 365)
        ],
        'gestor' => [
            'expiration_days' => env('RESET_PASSWORD_DAYS_GESTOR', 365)
        ]
    ],

    'migrated' => [
        'v1' => env('MIGRATED_V1', false),
    ],

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

    'env' => env('APP_ENV', 'production'),

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

    'url' => env('APP_URL', 'http://localhost'),
    'web_url' => env('APP_WEB_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

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

    'timezone' => env('TIMEZONE', 'America/Lima'),

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

    'locale' => 'es',

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

    'faker_locale' => 'es_PE',

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

    /*
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
        // 'store'  => 'redis',
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

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        // App\Providers\ChartServiceProvider::class,
        App\Providers\ValidationServiceProvider::class,
        Lab404\Impersonate\ImpersonateServiceProvider::class,

        Jenssegers\Mongodb\MongodbServiceProvider::class,
        // App\Providers\TenancyServiceProvider::class,

    ],

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
        // 'ExampleClass' => App\Example\ExampleClass::class,
    ])->toArray(),

];
