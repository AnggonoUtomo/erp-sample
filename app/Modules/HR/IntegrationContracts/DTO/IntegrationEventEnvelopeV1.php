<?php

namespace App\Modules\HR\IntegrationContracts\DTO;

use App\Modules\HR\IntegrationContracts\Support\ForbiddenIntegrationFieldGuard;
use Carbon\CarbonImmutable;

final readonly class IntegrationEventEnvelopeV1
{
    public const EVENT_VERSION = 1;

    public function __construct(
        public string $eventId,
        public string $eventName,
        public CarbonImmutable $occurredAt,
        public string $sourceModule,
        public ?int $actorUserId,
        public ?string $correlationId,
        public array $payload,
    ) {
        app(ForbiddenIntegrationFieldGuard::class)->assertSafePayload($payload);
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
        return [
            'eventId' => $this->eventId,
            'eventName' => $this->eventName,
            'eventVersion' => self::EVENT_VERSION,
            'occurredAt' => $this->occurredAt->utc()->format('Y-m-d\TH:i:s\Z'),
            'sourceModule' => $this->sourceModule,
            'actorUserId' => $this->actorUserId,
            'correlationId' => $this->correlationId,
            'payload' => $this->payload,
        ];
    }
}
