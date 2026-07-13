<?php

namespace App\Modules\HR\Employees\DTO;

use Illuminate\Http\UploadedFile;

final readonly class EmployeeData
{
    public function __construct(
        public ?int $userId,
        public ?int $supervisorId,
        public ?int $departementId,
        public ?int $positionId,
        public ?int $jobLevelId,
        public ?int $workLocationId,
        public ?int $employmentStatusId,
        public ?int $employmentTypeId,
        public string $employeeNumber,
        public string $firstName,
        public ?string $lastName,
        public string $displayName,
        public ?string $workEmail,
        public ?string $personalEmail,
        public ?string $phone,
        public ?string $dateOfBirth,
        public ?string $placeOfBirth,
        public ?string $nationalId,
        public ?string $address,
        public ?string $emergencyContactName,
        public ?string $emergencyContactPhone,
        public ?string $emergencyContactRelation,
        public ?string $hiredAt,
        public ?string $endedAt,
        public ?string $notes,
        public bool $active,
        public ?UploadedFile $avatar,
        public bool $removeAvatar,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?UploadedFile $avatar = null): self
    {
        $firstName = trim((string) $data['first_name']);
        $lastName = trim((string) ($data['last_name'] ?? '')) ?: null;
        $displayName = trim((string) ($data['display_name'] ?? '')) ?: trim($firstName.' '.($lastName ?? ''));

        return new self(
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            supervisorId: isset($data['supervisor_id']) ? (int) $data['supervisor_id'] : null,
            departementId: isset($data['departement_id']) ? (int) $data['departement_id'] : null,
            positionId: isset($data['position_id']) ? (int) $data['position_id'] : null,
            jobLevelId: isset($data['job_level_id']) ? (int) $data['job_level_id'] : null,
            workLocationId: isset($data['work_location_id']) ? (int) $data['work_location_id'] : null,
            employmentStatusId: isset($data['employment_status_id']) ? (int) $data['employment_status_id'] : null,
            employmentTypeId: isset($data['employment_type_id']) ? (int) $data['employment_type_id'] : null,
            employeeNumber: strtoupper((string) $data['employee_number']),
            firstName: $firstName,
            lastName: $lastName,
            displayName: $displayName,
            workEmail: trim((string) ($data['work_email'] ?? '')) ?: null,
            personalEmail: trim((string) ($data['personal_email'] ?? '')) ?: null,
            phone: trim((string) ($data['phone'] ?? '')) ?: null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            placeOfBirth: trim((string) ($data['place_of_birth'] ?? '')) ?: null,
            nationalId: trim((string) ($data['national_id'] ?? '')) ?: null,
            address: trim((string) ($data['address'] ?? '')) ?: null,
            emergencyContactName: trim((string) ($data['emergency_contact_name'] ?? '')) ?: null,
            emergencyContactPhone: trim((string) ($data['emergency_contact_phone'] ?? '')) ?: null,
            emergencyContactRelation: trim((string) ($data['emergency_contact_relation'] ?? '')) ?: null,
            hiredAt: $data['hired_at'] ?? null,
            endedAt: $data['ended_at'] ?? null,
            notes: trim((string) ($data['notes'] ?? '')) ?: null,
            active: (bool) ($data['active'] ?? true),
            avatar: $avatar,
            removeAvatar: (bool) ($data['remove_avatar'] ?? false),
        );
    }
}
