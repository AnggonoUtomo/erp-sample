<?php

namespace App\Integration\Contracts;

use App\Shared\ValueObjects\ModuleIdentifier;

interface IntegrationContext
{
    public function source(): ModuleIdentifier;

    public function target(): ?ModuleIdentifier;

    public function correlationId(): ?string;

    public function actorId(): int|string|null;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;
}
