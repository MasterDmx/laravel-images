<?php

namespace MasterDmx\LaravelImages;

use Illuminate\Contracts\Filesystem\Filesystem;

class Cell
{
    public function __construct(
        private CellPath $path,
        private Filesystem $filesystem,
    ) {}

    public function getPath(): CellPath
    {
        return $this->path;
    }

    /**
     * Возвращает путь до файла \ каталога внутри ячейки
     *
     * @param string $path
     *
     * @return string
     */
    public function getPathFor(string $path): string
    {
        return $this->filesystem->path($this->getInnerPathFor($path));
    }

    /**
     * Возвращает внутренний путь
     *
     * @param string $path
     *
     * @return string
     */
    public function getInnerPathFor(string $path): string
    {
        return $this->path->toString() . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Возвращает полный URL до $url
     *
     * @param string $url
     *
     * @return string
     */
    public function getUrlFor(string $url): string
    {
        return $this->filesystem->url($this->getInnerUrlFor($url));
    }

    /**
     * Возвращает URL относительно хранилища
     *
     * @param string $url
     *
     * @return string
     */
    public function getInnerUrlFor(string $url): string
    {
        return $this->path->toUrl() . '/' . $url;
    }

    /**
     * Возвращает содержимое файла $path внутри ячейки
     *
     * @param string $path
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get(string $path): string
    {
        return $this->filesystem->get($this->getInnerPathFor($path));
    }

    /**
     * Проверяет наличие файла $path внутри ячейки
     *
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool
    {
        return $this->filesystem->exists($this->getInnerPathFor($path));
    }

    /**
     * Создает файл $path с содержимым $content внутри ячейки
     *
     * @param string $path
     * @param        $content
     *
     * @return bool
     */
    public function put(string $path, $content): bool
    {
        return $this->filesystem->put($this->getInnerPathFor($path), $content);
    }
}
