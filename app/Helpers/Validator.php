<?php

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label = ''): self
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            $this->errors[] = ($label ?: $field) . ' es obligatorio';
        }
        return $this;
    }

    public function email(string $field, string $value, string $label = ''): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = ($label ?: $field) . ' no tiene un formato válido';
        }
        return $this;
    }

    public function minLength(string $field, string $value, int $min, string $label = ''): self
    {
        if (strlen($value) < $min) {
            $this->errors[] = ($label ?: $field) . " debe tener al menos $min caracteres";
        }
        return $this;
    }

    public function inArray(string $field, string $value, array $allowed, string $label = ''): self
    {
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[] = ($label ?: $field) . ' no es un valor válido';
        }
        return $this;
    }

    public function integer(string $field, mixed $value, string $label = ''): self
    {
        if ($value !== '' && $value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[] = ($label ?: $field) . ' debe ser un número entero';
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return $this->errors[0] ?? '';
    }

    public function clear(): void
    {
        $this->errors = [];
    }
}
