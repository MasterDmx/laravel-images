<?php

namespace MasterDmx\LaravelImages;

class Meta
{
    /**
     * Название изображения
     *
     * @var string
     */
    public string $name;

    /**
     * Заголовок
     *
     * @var string
     */
    public string $title;

    /**
     * Описание
     *
     * @var string
     */
    public string $description;

    public function __construct(string $name, string $title, string $description)
    {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['name'] ?? '',
            $data['title'] ?? '',
            $data['description'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
