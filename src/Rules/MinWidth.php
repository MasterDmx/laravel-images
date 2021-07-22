<?php

namespace MasterDmx\LaravelImages\Rules;

use Intervention\Image\Image;
use MasterDmx\LaravelImages\Contracts\Rule;

class MinWidth implements Rule
{
    public function check(Image $image, $value): bool
    {
        return $image->width() >= $value;
    }

    public function getMessage(): string
    {
        return 'Image width is too small';
    }
}
