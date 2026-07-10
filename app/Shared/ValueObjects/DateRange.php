<?php

namespace App\Shared\ValueObjects;

use App\Shared\Exceptions\SharedKernelException;
use Carbon\CarbonImmutable;

final readonly class DateRange
{
    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {
        if ($this->end->lessThan($this->start)) {
            throw SharedKernelException::invalidValueObject(self::class, 'end date must be greater than or equal to start date.');
        }
    }

    public static function fromStrings(string $start, string $end): self
    {
        return new self(CarbonImmutable::parse($start)->startOfDay(), CarbonImmutable::parse($end)->endOfDay());
    }

    public function contains(CarbonImmutable|string $date): bool
    {
        $value = is_string($date) ? CarbonImmutable::parse($date) : $date;

        return $value->betweenIncluded($this->start, $this->end);
    }

    public function days(): int
    {
        return (int) $this->start->startOfDay()->diffInDays($this->end->startOfDay()) + 1;
    }

    /**
     * @return array{start: string, end: string}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
        ];
    }
}
