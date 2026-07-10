<?php

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\SharedKernelException;
use Illuminate\Support\Str;

final readonly class ModuleIdentifier
{
    public function __construct(
        public string $project,
        public string $module,
    ) {
        if ($this->project === '' || $this->module === '') {
            throw SharedKernelException::invalidValueObject(self::class, 'project and module are required.');
        }
    }

    public static function parse(string $value): self
    {
        $parts = preg_split('/[.:\/\\\\]+/', $value, flags: PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) !== 2) {
            throw SharedKernelException::invalidValueObject(self::class, 'expected format Project.Module.');
        }

        return new self(Str::studly($parts[0]), Str::studly($parts[1]));
    }

    public function key(): string
    {
        return "{$this->project}.{$this->module}";
    }

    public function namespace(): string
    {
        return "App\\Modules\\{$this->project}\\{$this->module}";
    }

    public function frontendPath(): string
    {
        return Str::of($this->project)->kebab().'/'.Str::of($this->module)->kebab();
    }
}
