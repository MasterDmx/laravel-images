<?php

namespace MasterDmx\LaravelImages\Contracts;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

interface Variation extends FilterInterface
{
    /**
     * Название вариации
     *
     * @return string
     */
    public function name(): string;

    /**
     * Условия срабатывания вариации
     *
     * @param Image $image
     *
     * @return bool
     */
    public function condition(Image $image): bool;

    /**
     * Применение изменений
     *
     * @param Image $image
     *
     * @return Image
     */
    public function applyFilter(Image $image): Image;
}
