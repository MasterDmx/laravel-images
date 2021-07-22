<?php

namespace MasterDmx\LaravelImages;

use MasterDmx\LaravelImages\Contracts\Rule;
use MasterDmx\LaravelImages\Exceptions\ValidationException;

class Validator
{
    /**
     * Обработчики правил
     *
     * @var array|string[]
     */
    private array $ruleHandlers = [
        'min_height' => \MasterDmx\LaravelImages\Rules\MinHeight::class,
        'min_width' => \MasterDmx\LaravelImages\Rules\MinWidth::class,
    ];

    /**
     * Ошибки валидации
     *
     * @var array
     */
    private array $errors = [];

    public function validate(\Intervention\Image\Image $image, array $rules): void
    {
        foreach ($rules as $ruleName => $value) {
            if (isset($this->ruleHandlers[$ruleName])) {
                $class = $this->ruleHandlers[$ruleName];

                /** @var Rule $rule */
                $rule = new $class();

                if (!$rule->check($image, $value)) {
                    $this->errors[$ruleName] = $rule->getMessage();
                }
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }
}
