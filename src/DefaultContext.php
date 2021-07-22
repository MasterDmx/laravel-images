<?php

namespace MasterDmx\LaravelImages;

use MasterDmx\LaravelImages\Contracts\Context;

class DefaultContext implements Context
{
    public static function name(): string
    {
        return 'default';
    }

    public function rules(): array
    {
        return [];
    }

    public function variations(): array
    {
        return [];
    }
}
