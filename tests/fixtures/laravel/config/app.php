<?php

return [

    'name' => env('APP_NAME', 'Laravel'),

    'env' => env('APP_ENV', 'production'),

    'debug' => env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'UTC',

    'locale' => 'en',

    'fallback_locale' => 'en',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'log' => env('APP_LOG', 'single'),

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    'providers' => [

        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Z1px\Http\LaravelServiceProvider::class,
        Z1px\Http\Tests\Fixtures\Laravel\App\Providers\RouteServiceProvider::class,
        Z1px\Http\Tests\Fixtures\Laravel\App\Providers\TestServiceProvider::class,

    ],

    'aliases' => [
        //
    ],

];
