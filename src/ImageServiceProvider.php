<?php

namespace MasterDmx\LaravelImages;

use Illuminate\Support\ServiceProvider;
use MasterDmx\LaravelMedia\Contexts\ContextRegistry;

class ImageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__.'/../config/images.php' => config_path('images.php')], 'config');

        /** @var ImageLibrary $library */
        $library = $this->app->get(ImageLibrary::class);

        // Регистрация контекстов из конфига
        foreach (config('images.contexts', []) as $context) {
            $library->registerContext($context);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/../config/images.php', 'images');

        // Добавляем диск в диски ларавел
        config(['filesystems.disks.' . config('images.disk') => config('images.disk_settings')]);

        // Привязываем синглтон сервис-класса
        $this->app->singleton(ImageLibrary::class);
    }
}
