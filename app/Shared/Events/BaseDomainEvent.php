<?php

namespace App\Shared\Events;

use App\Shared\Contracts\DomainEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

abstract class BaseDomainEvent implements DomainEvent
{
    private string $eventId;

    private CarbonImmutable $occurredAt;

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private readonly string $aggregateId,
        private readonly array $payload = [],
        private readonly array $metadata = [],
        ?string $eventId = null,
        ?CarbonImmutable $occurredAt = null,
    ) {
        $this->eventId = $eventId ?? (string) Str::uuid();
        $this->occurredAt = $occurredAt ?? CarbonImmutable::now();
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function eventName(): string
    {
        return Str::of(class_basename(static::class))->kebab()->toString();
    }

    public function occurredAt(): CarbonImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->eventId(),
            'name' => $this->eventName(),
            'aggregate_id' => $this->aggregateId(),
            'payload' => $this->payload(),
            'metadata' => $this->metadata(),
            'occurred_at' => $this->occurredAt()->toISOString(),
        ];
    }
}
