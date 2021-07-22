<?php

namespace MasterDmx\LaravelImages;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use MasterDmx\LaravelImages\Contracts\Context;
use MasterDmx\LaravelImages\Exceptions\ContextNotFoundException;

class ImageLibrary
{
    /**
     * Зарегистрированные классы контекстов
     *
     * @var array
     */
    private array $contextsLazy;

    /**
     * Контексты
     *
     * @var array
     */
    private array $contexts;

    /**
     * Добавляет изображения по загружаемому laravel файлу
     *
     * @param UploadedFile        $file
     * @param string|null         $name
     * @param string|Context|null $context
     *
     * @return Image
     */
    public function add(UploadedFile $file, ?string $name = null, string|Context|null $context = null): Image
    {
        $intervention = Support::initInterventionImage($file);

        if (empty($context)) {
            $context = app(config('images.default_context'));
        } elseif (is_string($context)) {
            $context = $this->getContext($context);
        }

        // Валидация по правилам контекста
        $validator = new Validator();
        $validator->validate($intervention, $context->rules());

        // Создаем ячейку под изображение
        $cell = $this->createCell();

        // Инциаилизируем объект изображения
        $image = Image::new($cell, $intervention,!empty($name) ? $name : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

        // Применяем текущий контекст к изображению
        $image->applyContext($context);

        return $image;
    }

    public function all()
    {
        $list = [];

        foreach ($this->scan() as $path){
            try {
                $list[] = $this->find($path);
            } catch (\Exception $e) {
                continue;
            }
        }

        return new ImageCollection($list);
    }

    /**
     * Получает изображение
     *
     * @param string $path
     *
     * @return Image
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function find(string $path): Image
    {
        return Image::init($this->getCell(CellPath::parse($path)));
    }

    /**
     * Возвращает объект контекста по названию
     *
     * @param string $name
     *
     * @return Context
     */
    public function getContext(string $name): Context
    {
        if (isset($this->contexts[$name])) {
            return $this->contexts[$name];
        } elseif (isset($this->contextsLazy[$name])) {
            return app($this->contextsLazy[$name]);
        }

        throw new ContextNotFoundException('Context ' . $name . ' not found');
    }

    /**
     * Регистрирует контекст
     *
     * @param Context|string $context
     *
     * @return $this
     */
    public function registerContext(Context|string $context): static
    {
        if (is_object($context)) {
            $this->contexts[$context::name()] = $context;
        } else {
            $this->contextsLazy[$context::name()] = $context;
        }

        return $this;
    }

    /**
     * Просканировать диск на наличие изображений
     *
     * @param string|null $directory
     * @param int         $level
     * @param string      $path
     *
     * @return array
     */
    private function scan(?string $directory = null, int $level = 1, string $path = ''): array
    {
        $result = [];
        $nextLevel = $level + 1;
        $directory = $directory ?? '';

        foreach (scandir($this->getFilesystem()->path($directory)) as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            if (substr($path, 0, 1) === '\\') {
                $path = substr($path, 1);
            }

            if ($level === 3) {
                $result[] = $path . DIRECTORY_SEPARATOR . $name;
            } else {
                $result = array_merge($result, $this->scan($directory . DIRECTORY_SEPARATOR . $name, $nextLevel, $path . DIRECTORY_SEPARATOR . $name));
            }
        }

        return $result;
    }

    /**
     * Возвращает объект существующей ячейки
     *
     * @param CellPath $path
     *
     * @return Cell
     */
    private function getCell(CellPath $path)
    {
        return new Cell($path, $this->getFilesystem());
    }

    /**
     * Создает новую ячейку
     *
     * @return Cell
     */
    private function createCell(): Cell
    {
        $path = CellPath::create();

        while ($this->checkExists((string)$path)) {
            $path = CellPath::create();
        }

        mkdir($this->getFullPathFor((string)$path), 0777, true);

        return new Cell($path, $this->getFilesystem());
    }

    /**
     * Проверяет файла \ каталога по локальному пути внутри хранилища
     *
     * @param string $path
     *
     * @return bool
     */
    private function checkExists(string $path): bool
    {
        return file_exists($this->getFullPathFor($path));
    }

    /**
     * Возвращает полный путь по локальному пути внутри хранилища
     *
     * @param string $path
     *
     * @return string
     */
    private function getFullPathFor(string $path): string
    {
        return $this->getFilesystem()->path($path);
    }

    /**
     * Возращает файловое хранилище лары
     *
     * @return Filesystem
     */
    private function getFilesystem(): Filesystem
    {
        return \Illuminate\Support\Facades\Storage::disk(config('images.disk'));
    }
}
