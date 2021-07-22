<?php

namespace MasterDmx\LaravelImages;

use Carbon\Carbon;
use Intervention\Image\Image as InterventionImage;
use MasterDmx\LaravelImages\Contracts\Context;
use MasterDmx\LaravelImages\Contracts\Variation;
use MasterDmx\LaravelImages\Exceptions\IncorrectMetaDataException;
use MasterDmx\LaravelImages\Exceptions\MetaFileNotFoundException;
use MasterDmx\LaravelImages\Exceptions\OriginalImageFileNotFoundException;

class Image
{
    /**
     * Название мета файла
     */
    public const META_FILE_NAME = 'meta.json';

    /**
     * Разделитель названия вариаций
     */
    public const VARIATION_SEPARATOR = '.';

    /**
     * Название оригинального файла
     *
     * @var string
     */
    private string $name;

    /**
     * Расширение оригинального файла
     *
     * @var string
     */
    private string $extension;

    /**
     * Заголовок
     *
     * @var string
     */
    private string $title = '';

    /**
     * Описание
     *
     * @var string
     */
    private string $description = '';

    /**
     * Дата создания
     *
     * @var Carbon
     */
    private Carbon $created_at;

    /**
     * Дата последнего обновления
     *
     * @var Carbon
     */
    private Carbon $updated_at;

    /**
     * Вариации
     *
     * @var array
     */
    private array $variations;

    /**
     * Ячейка хранилища
     *
     * @var Cell
     */
    private Cell $cell;

    /**
     * @var InterventionImage
     */
    private InterventionImage $intervention;

    private function __construct(Cell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * Вовзвращает имя файла изображения
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает расширение оригинального файла
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Возвращает имя файла с расширение
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->getName() . '.' . $this->getExtension();
    }

    /**
     * Вовзвращает заголовок
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Вовзвращает заголовок
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Возвращает дату создания
     *
     * @return Carbon
     */
    public function getCreatedDate(): Carbon
    {
        return $this->created_at;
    }

    /**
     * Возвращает дату обновления
     *
     * @return Carbon
     */
    public function getUpdatedDate(): Carbon
    {
        return $this->updated_at;
    }

    /**
     * Возвращает путь до изображения относительно библиотеки
     *
     * @return string
     */
    public function getInnerPath(): string
    {
        return $this->cell->getInnerPathFor($this->getFilename());
    }

    /**
     * Возвращает URL до оригинального изображения
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->cell->getUrlFor($this->getFilename());
    }

    // ----------------------------------------------------------
    // Manage
    // ----------------------------------------------------------

    /**
     * Инициализирует оюъект для существующего изображения
     *
     * @param Cell $cell
     *
     * @return static
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function init(Cell $cell): static
    {
        // Проверяем наличие мета файла
        if (!$cell->has(static::META_FILE_NAME)){
            throw new MetaFileNotFoundException('Meta file not found for in ' . $cell->getPath()->toString());
        }

        // Загружаем сырые мета данные
        $meta = json_decode($cell->get(static::META_FILE_NAME), true);

        // Проверяем, что мета данные массив, и что в нем есть информация об оригинальном изображении
        if (!is_array($meta) || empty($meta['name']) || empty($meta['extension'])) {
            throw new IncorrectMetaDataException();
        }

        $filename = $meta['name'] . '.' . $meta['extension'];

        // Проверяем, что файл оригинального изображения существует
        if (!$cell->has($filename)) {
            throw new OriginalImageFileNotFoundException('Original image file (' . $filename . ') not found in ' . $cell->getPath()->toString());
        }

        $image = new static($cell);

        $image->name = $meta['name'];
        $image->extension = $meta['extension'];
        $image->title = $meta['title'] ?? '';
        $image->description = $meta['description'] ?? '';
        $image->created_at  = Carbon::parse($meta['created_at']);
        $image->updated_at  = Carbon::parse($meta['updated_at']);

        return $image;
    }

    /**
     * Создает новое изображение
     *
     * @param Cell              $cell
     * @param InterventionImage $intervention
     * @param string            $name
     *
     * @return Image
     */
    public static function new(Cell $cell, InterventionImage $intervention, string $name): static
    {
        $name = Support::processName($name);
        $extension = Support::getExtensionByMime($intervention->mime());
        $path = $cell->getPathFor($name . '.' . $extension);

        // Сохраняем изображение в ячейке
        $intervention->save($path);

        // Инициализация объекта
        $image = new static($cell);

        // Устанавливаем имя файла
        $image->name = $name;

        // Устанавливаем расширение
        $image->extension = $extension;

        // Устанавливаем дату создания
        $image->created_at = Carbon::now();

        // Сохранение данных в мета файл
        $image->save();

        // Устанавливаем объект intervention
        $image->intervention = $intervention;

        return $image;
    }

    /**
     * Применяет вариации контекста
     *
     * @param Context|string $context
     *
     * @return $this
     */
    public function applyContext(Context|string $context): static
    {
        if (is_string($context)){
            $context = app(ImageLibrary::class)->getContext($context);
        }

        foreach ($context->variations() as $class) {
            $this->applyVariation(app($class));
        }

        return $this;
    }

    /**
     * Применяет вариацию
     */
    public function applyVariation(Variation $variation): bool
    {
        $intervention = $this->getIntervention();

        if (!$variation->condition($intervention)) {
            return false;
        }

        // Клонирование оригинального изображения
        $clone = clone $intervention;

        // Название вариации
        $name = $this->name . static::VARIATION_SEPARATOR . $variation->name() . '.' . $this->extension;

        // Применение фильтра + сохранение
        $clone->filter($variation)->save($this->cell->getPathFor($name));

        // Вносим вариацию в список
        $this->variations[$variation->name()] = $name;

        return true;
    }

    /**
     * Задает заголовок
     *
     * @param string $title
     *
     * @return Image
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Задает описание
     *
     * @param string $description
     *
     * @return Image
     */
    public function setDescriptions(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Сохраняет изменения
     *
     * @return $this
     */
    public function save(): static
    {
        $this->cell->put(static::META_FILE_NAME, json_encode([
            'name' => $this->name,
            'extension' => $this->extension,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at->toString(),
            'updated_at' => Carbon::now(),
        ]));

        return $this;
    }

    // ----------------------------------------------------------
    // Support
    // ----------------------------------------------------------

    /**
     * Возвращает объект InterventionImage
     *
     * @return InterventionImage
     */
    private function getIntervention(): InterventionImage
    {
        if (isset($this->intervention)) {
            return $this->intervention;
        }

        return $this->intervention = Support::initInterventionImage($this->cell->getPathFor($this->getFilename()));
    }
}
