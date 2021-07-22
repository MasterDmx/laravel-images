<?php

namespace MasterDmx\LaravelImages\Rules;

use Intervention\Image\Image;
use MasterDmx\LaravelImages\Contracts\Rule;

class MinHeight implements Rule
{
    public function check(Image $image, $value): bool
    {
        return $image->height() >= $value;
    }

    public function getMessage(): string
    {
        return 'Image height is too small';
    }
}
