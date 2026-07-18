<?php

namespace App\Modules\HR\IntegrationContracts\Services;

use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeDocumentComplianceSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeDocumentComplianceSnapshotV1;
use Carbon\CarbonImmutable;

class EloquentEmployeeDocumentComplianceSnapshotProvider implements EmployeeDocumentComplianceSnapshotProvider
{
    public function forEmployee(int $employeeId, string $asOf, int $warningDays = 30): ?EmployeeDocumentComplianceSnapshotV1
    {
        if (! Employee::query()->whereKey($employeeId)->exists()) {
            return null;
        }

        $asOfDate = CarbonImmutable::createFromFormat('!Y-m-d', $asOf);
        $warningThrough = $asOfDate->addDays($warningDays);

        $documents = EmployeeDocument::query()
            ->where('employee_id', $employeeId)
            ->get(['id', 'verification_status', 'expires_at']);

        $expiredCount = 0;
        $expiringCount = 0;

        foreach ($documents as $document) {
            if ($document->expires_at === null) {
                continue;
            }

            $expiresAt = CarbonImmutable::instance($document->expires_at);

            if ($expiresAt->lessThan($asOfDate)) {
                $expiredCount++;
            } elseif ($expiresAt->lessThanOrEqualTo($warningThrough)) {
                $expiringCount++;
            }
        }

        return new EmployeeDocumentComplianceSnapshotV1(
            employeeId: $employeeId,
            requiredCount: $documents->count(),
            verifiedCount: $documents->where('verification_status', 'VERIFIED')->count(),
            pendingCount: $documents->where('verification_status', 'PENDING')->count(),
            expiredCount: $expiredCount,
            expiringCount: $expiringCount,
            asOf: $asOf,
        );
    }
}
