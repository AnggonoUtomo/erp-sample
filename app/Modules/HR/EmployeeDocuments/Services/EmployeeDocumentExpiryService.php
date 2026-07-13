<?php

namespace App\Modules\HR\EmployeeDocuments\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class EmployeeDocumentExpiryService
{
    public const STATES = ['NOT_APPLICABLE', 'VALID', 'EXPIRING', 'EXPIRED'];

    public function state(?CarbonInterface $expiresAt, CarbonImmutable $asOf, int $warningDays): string
    {
        if ($expiresAt === null) {
            return 'NOT_APPLICABLE';
        }

        $expiryDate = CarbonImmutable::instance($expiresAt)->startOfDay();
        $asOf = $asOf->startOfDay();

        if ($expiryDate->isBefore($asOf)) {
            return 'EXPIRED';
        }

        return $expiryDate->lessThanOrEqualTo($asOf->addDays($warningDays)) ? 'EXPIRING' : 'VALID';
    }

    public function applyState(Builder $query, string $state, CarbonImmutable $asOf, int $warningDays): Builder
    {
        $date = $asOf->toDateString();
        $warningThrough = $asOf->addDays($warningDays)->toDateString();

        return match ($state) {
            'NOT_APPLICABLE' => $query->whereNull('expires_at'),
            'EXPIRED' => $query->whereDate('expires_at', '<', $date),
            'EXPIRING' => $query->whereBetween('expires_at', [$date, $warningThrough]),
            'VALID' => $query->whereDate('expires_at', '>', $warningThrough),
            default => $query,
        };
    }
}
