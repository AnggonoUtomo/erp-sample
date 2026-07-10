<?php

namespace App\Shared\Contracts;

use Carbon\CarbonImmutable;

interface DomainEvent
{
    public function eventId(): string;

    public function eventName(): string;

    public function occurredAt(): CarbonImmutable;

    public function aggregateId(): string;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
