<?php

namespace MasterDmx\LaravelImages\Contracts;

use Intervention\Image\Image;

interface Rule
{
    public function check(Image $image, $value): bool;

    public function getMessage(): string;
}
