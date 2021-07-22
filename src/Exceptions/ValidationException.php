<?php

namespace MasterDmx\LaravelImages\Exceptions;

use Throwable;

class ValidationException extends \RuntimeException
{
    private array $errors;

    public function __construct(array $errors, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;

        parent::__construct('Validation error', $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
