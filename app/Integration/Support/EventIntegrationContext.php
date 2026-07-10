<?php

namespace App\Integration\Support;

use App\Integration\Contracts\IntegrationContext;
use App\Shared\Contracts\DomainEvent;
use App\Shared\ValueObjects\ModuleIdentifier;

final readonly class EventIntegrationContext implements IntegrationContext
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private ModuleIdentifier $source,
        private ?ModuleIdentifier $target = null,
        private ?string $correlationId = null,
        private int|string|null $actorId = null,
        private array $metadata = [],
    ) {}

    public static function fromEvent(DomainEvent $event, ModuleIdentifier $source, ?ModuleIdentifier $target = null): self
    {
        $metadata = $event->metadata();

        return new self(
            source: $source,
            target: $target,
            correlationId: isset($metadata['correlation_id']) ? (string) $metadata['correlation_id'] : null,
            actorId: $metadata['actor_id'] ?? null,
            metadata: $metadata,
        );
    }

    public function source(): ModuleIdentifier
    {
        return $this->source;
    }

    public function target(): ?ModuleIdentifier
    {
        return $this->target;
    }

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function actorId(): int|string|null
    {
        return $this->actorId;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
