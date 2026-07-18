<?php

namespace App\Modules\HR\IntegrationContracts\Events;

use App\Modules\HR\IntegrationContracts\DTO\IntegrationEventEnvelopeV1;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationEventRegistry;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class HRIntegrationEventV1
{
    private function __construct(public IntegrationEventEnvelopeV1 $envelope) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function make(
        string $eventName,
        CarbonImmutable $occurredAt,
        ?int $actorUserId,
        ?string $correlationId,
        array $payload,
        ?string $eventId = null,
    ): self {
        $registry = app(HRIntegrationEventRegistry::class);

        if (! $registry->has($eventName)) {
            throw new InvalidArgumentException("Unknown HR integration event contract: {$eventName}.");
        }

        $occurredAtUtc = $occurredAt->utc()->format('Y-m-d\TH:i:s\Z');
        $employeeId = (string) ($payload['employeeId'] ?? 'na');

        return new self(new IntegrationEventEnvelopeV1(
            eventId: $eventId ?? "hr-integration:{$eventName}:{$employeeId}:{$occurredAtUtc}",
            eventName: $eventName,
            occurredAt: $occurredAt,
            sourceModule: (string) $registry->sourceModuleFor($eventName),
            actorUserId: $actorUserId,
            correlationId: $correlationId,
            payload: $payload,
        ));
    }

    /**
     * @return array{
     *     eventId: string,
     *     eventName: string,
     *     eventVersion: int,
     *     occurredAt: string,
     *     sourceModule: string,
     *     actorUserId: ?int,
     *     correlationId: ?string,
     *     payload: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return $this->envelope->toArray();
    }
}
