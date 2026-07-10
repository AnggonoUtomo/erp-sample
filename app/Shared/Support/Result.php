<?php

namespace App\Shared\Support;

final readonly class Result
{
    /**
     * @param  array<string, mixed>  $meta
     */
    private function __construct(
        private bool $successful,
        private mixed $value = null,
        private ?string $error = null,
        private array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(mixed $value = null, array $meta = []): self
    {
        return new self(true, $value, null, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function failure(string $error, array $meta = []): self
    {
        return new self(false, null, $error, $meta);
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    public function failed(): bool
    {
        return ! $this->successful;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * @return array{successful: bool, value: mixed, error: string|null, meta: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'successful' => $this->successful(),
            'value' => $this->value(),
            'error' => $this->error(),
            'meta' => $this->meta(),
        ];
    }
}
