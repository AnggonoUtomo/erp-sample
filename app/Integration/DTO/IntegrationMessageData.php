<?php

namespace App\Integration\DTO;

use App\Shared\Contracts\DomainEvent;
use App\Shared\DTO\DataObject;
use App\Shared\ValueObjects\ModuleIdentifier;
use Carbon\CarbonImmutable;

final readonly class IntegrationMessageData extends DataObject
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $eventId,
        public string $eventName,
        public string $aggregateId,
        public ModuleIdentifier $source,
        public ?ModuleIdentifier $target,
        public array $payload,
        public array $metadata,
        public CarbonImmutable $occurredAt,
    ) {}

    public static function fromDomainEvent(DomainEvent $event, ModuleIdentifier $source, ?ModuleIdentifier $target = null): self
    {
        return new self(
            eventId: $event->eventId(),
            eventName: $event->eventName(),
            aggregateId: $event->aggregateId(),
            source: $source,
            target: $target,
            payload: $event->payload(),
            metadata: $event->metadata(),
            occurredAt: $event->occurredAt(),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): static
    {
        return new self(
            eventId: (string) $payload['event_id'],
            eventName: (string) $payload['event_name'],
            aggregateId: (string) $payload['aggregate_id'],
            source: ModuleIdentifier::parse((string) $payload['source']),
            target: isset($payload['target']) && $payload['target'] !== null
                ? ModuleIdentifier::parse((string) $payload['target'])
                : null,
            payload: is_array($payload['payload'] ?? null) ? $payload['payload'] : [],
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
            occurredAt: CarbonImmutable::parse((string) $payload['occurred_at']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName,
            'aggregate_id' => $this->aggregateId,
            'source' => $this->source->key(),
            'target' => $this->target?->key(),
            'payload' => $this->payload,
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt->toISOString(),
        ];
    }
}
