<?php

return [
    'contexts' => [

    ],

    'default_context' => \MasterDmx\LaravelImages\DefaultContext::class,

    // Идентификатор диска
    'disk' => 'images',

    // Параметры диска
    'disk_settings' => [
        'driver' => 'local',
        'root' => storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images'),
        'url' => env('APP_URL').'/storage/images',
        'visibility' => 'public',
    ],
];
