<?php

namespace App\Modules\HR\EmployeeDocuments\DTO;

readonly class EmployeeDocumentData
{
    public function __construct(
        public int $employeeId,
        public int $documentTypeId,
        public ?string $documentNumber,
        public ?string $issuer,
        public ?string $issuedAt,
        public ?string $expiresAt,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        $optional = fn (string $field): ?string => filled($data[$field] ?? null) ? trim((string) $data[$field]) : null;

        return new self(
            employeeId: (int) $data['employee_id'], documentTypeId: (int) $data['document_type_id'],
            documentNumber: $optional('document_number'), issuer: $optional('issuer'),
            issuedAt: $optional('issued_at'), expiresAt: $optional('expires_at'), notes: $optional('notes'),
        );
    }
}
