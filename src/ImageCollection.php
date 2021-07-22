<?php

namespace MasterDmx\LaravelImages;

use Illuminate\Support\Collection;

/**
 * Class ImageCollection
 *
 * @property Image[] $items
 *
 * @method Image[] all()
 *
 * @package MasterDmx\LaravelImages
 */
class ImageCollection extends Collection
{
    /**
     * Сортирует коллекцию по отметке времени добавления
     *
     * @return static
     */
    public function sortByCreatedDate(): static
    {
        return $this->sortBy(fn ($image) => $image->getCreatedDate()->timestamp);
    }

    /**
     * Сортирует коллекцию по отметке времени добавления в обратном порядке
     *
     * @return static
     */
    public function sortByCreatedDateDesc(): static
    {
        return $this->sortBy(fn ($image) => -$image->getCreatedDate()->timestamp);
    }



    /**
     * Выводит \ выполняет $then, если в коллекции есть изображение
     *
     * @param string $id
     * @param mixed  $than
     * @param mixed  $else
     *
     * @return mixed
     */
    public function ifHas(string $id, mixed $than, mixed $else = null): mixed
    {
        if ($this->has($id)) {
            if (is_callable($than)) {
                return $than($this->get($id));
            } else {
                return $than;
            }
        }

        return $else;
    }
}
