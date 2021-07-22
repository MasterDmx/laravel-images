<?php

namespace MasterDmx\LaravelImages;

use MasterDmx\LaravelImages\Exceptions\IncorrectImagePathException;

class CellPath
{
    private function __construct(
        private string $dateFolder,
        private string $hashFolder,
        private string $id
    ) {}

    /**
     * Генерирует путь до ячейки
     *
     * @return static
     */
    public static function create(): static
    {
        $id = Support::replaceDotAnDash(uniqid('', true));
        $hashFolder = substr(md5($id), 0, 2);
        $dateFolder = date('Y-m');

        return new static($dateFolder, $hashFolder, $id);
    }

    /**
     * Инициализирует объект по строке пути
     *
     * @param string $path
     *
     * @return static
     */
    public static function parse(string $path): static
    {
        $processed = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $parsed = explode(DIRECTORY_SEPARATOR, $processed);

        if (empty($parsed[1]) || empty($parsed[2])) {
            throw new IncorrectImagePathException('Incorrect path ' . $path);
        }

        return new static($parsed[0], $parsed[1], $parsed[2]);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return implode(DIRECTORY_SEPARATOR, $this->toArray());
    }

    public function toUrl(): string
    {
        return implode('/', $this->toArray());
    }

    public function toArray(): array
    {
        return [
            $this->dateFolder,
            $this->hashFolder,
            $this->id,
        ];
    }
}
