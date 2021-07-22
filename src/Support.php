<?php

namespace MasterDmx\LaravelImages;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManager;

class Support
{
    /**
     * Заменяет все точки в строка на тире
     *
     * @param string $str
     *
     * @return string
     */
    public static function replaceDotAnDash(string $str): string
    {
        return str_replace('.', '-', $str);
    }

    public static function processName(string $name): string
    {
        $name = Str::slug($name);

        if(strlen($name) > 50) {
            $name = substr($name, 0, 50);
        }

        return $name;
    }

    public static function initInterventionImage(string $path): InterventionImage
    {
        return app(ImageManager::class)->make($path);
    }

    /**
     * Возвращает расширение изображения
     *
     * @param string $mime
     *
     * @return string
     */
    public static function getExtensionByMime(string $mime): string
    {
        $mimes = [
            'image/gif'     => 'gif',
            'image/jpeg'    => 'jpeg',
            'image/png'     => 'png',
            'image/svg+xml' => 'svg',
            'image/webp'    => 'webp',
        ];

        if (!isset($mimes[$mime])) {
            throw new UndefinedMimeTypeException('It is impossible to determine the file extension, since an unknown MIME type has come');
        }

        return $mimes[$mime];
    }

}
