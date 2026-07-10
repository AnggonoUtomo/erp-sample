<?php

namespace App\Modules\HR\Employees\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Employees\DTO\EmployeeData;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Employees\Transactions\EmployeesTransaction;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EmployeesService
{
    private const AUDIT_FIELDS = [
        'user_id',
        'departement_id',
        'position_id',
        'job_level_id',
        'work_location_id',
        'employment_status_id',
        'employment_type_id',
        'employee_number',
        'first_name',
        'last_name',
        'display_name',
        'work_email',
        'personal_email',
        'phone',
        'hired_at',
        'ended_at',
        'notes',
        'active',
    ];

    public function __construct(
        private readonly EmployeesTransaction $transaction,
        private readonly AuditLogService $audit,
        private readonly SystemSettingService $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPageData(array $filters = []): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $departement = $filters['departement'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $archive = $filters['archive'] ?? 'active';
        $perPage = $this->resolvePerPage($filters);

        $employees = Employee::query()
            ->with(['user', 'departement', 'position', 'jobLevel', 'workLocation', 'employmentStatus', 'employmentType', 'media'])
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('work_email', 'like', "%{$search}%")
                        ->orWhere('personal_email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($departement !== 'all', fn (Builder $query) => $query->where('departement_id', $departement))
            ->when($status !== 'all', fn (Builder $query) => $query->where('active', $status === 'active'))
            ->orderBy('display_name')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'employees' => $this->transformPaginator($employees),
            'options' => $this->options(),
            'filters' => [
                'search' => $search,
                'departement' => $departement,
                'status' => $status,
                'archive' => $archive,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => Employee::query()->count(),
                'active' => Employee::query()->where('active', true)->count(),
                'inactive' => Employee::query()->where('active', false)->count(),
                'linked_users' => Employee::query()->whereNotNull('user_id')->count(),
                'archived' => Employee::onlyTrashed()->count(),
            ],
        ];
    }

    public function create(EmployeeData $data): Employee
    {
        return $this->transaction->run(function () use ($data) {
            $employee = Employee::query()->create($this->payload($data));
            $this->syncAvatar($employee, $data);

            $this->audit->record(
                module: 'hr.employees',
                event: 'Employee.created',
                auditable: $employee,
                description: "Created Employee {$employee->employee_number}",
                newValues: [
                    ...$employee->only(self::AUDIT_FIELDS),
                    'avatar_changed' => (bool) $data->avatar,
                ],
            );

            return $employee->refresh();
        });
    }

    public function update(Employee $employee, EmployeeData $data): Employee
    {
        return $this->transaction->run(function () use ($employee, $data) {
            $oldValues = $employee->only(self::AUDIT_FIELDS);

            $employee->update($this->payload($data));
            $this->syncAvatar($employee, $data);

            $this->audit->record(
                module: 'hr.employees',
                event: 'Employee.updated',
                auditable: $employee,
                description: "Updated Employee {$employee->employee_number}",
                oldValues: $oldValues,
                newValues: [
                    ...$employee->only(self::AUDIT_FIELDS),
                    'avatar_changed' => (bool) $data->avatar,
                    'avatar_removed' => $data->removeAvatar,
                ],
            );

            return $employee->refresh();
        });
    }

    public function delete(Employee $employee): void
    {
        $this->transaction->run(function () use ($employee) {
            $oldValues = $employee->only(self::AUDIT_FIELDS);
            $employee->delete();

            $this->audit->record(
                module: 'hr.employees',
                event: 'Employee.deleted',
                auditable: $employee,
                description: "Archived Employee {$oldValues['employee_number']}",
                oldValues: $oldValues,
            );
        });
    }

    public function restore(Employee $employee): void
    {
        $this->transaction->run(function () use ($employee) {
            $oldValues = $employee->only(self::AUDIT_FIELDS);
            $employee->restore();

            $this->audit->record(
                module: 'hr.employees',
                event: 'Employee.restored',
                auditable: $employee,
                description: "Restored Employee {$employee->employee_number}",
                oldValues: $oldValues,
                newValues: $employee->fresh()?->only(self::AUDIT_FIELDS),
            );
        });
    }

    public function forceDelete(Employee $employee): void
    {
        $this->transaction->run(function () use ($employee) {
            $oldValues = $employee->only(self::AUDIT_FIELDS);

            $this->audit->record(
                module: 'hr.employees',
                event: 'Employee.force-deleted',
                auditable: $employee,
                description: "Force deleted Employee {$oldValues['employee_number']}",
                oldValues: $oldValues,
            );

            $employee->forceDelete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(EmployeeData $data): array
    {
        return [
            'user_id' => $data->userId,
            'departement_id' => $data->departementId,
            'position_id' => $data->positionId,
            'job_level_id' => $data->jobLevelId,
            'work_location_id' => $data->workLocationId,
            'employment_status_id' => $data->employmentStatusId,
            'employment_type_id' => $data->employmentTypeId,
            'employee_number' => $data->employeeNumber,
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'display_name' => $data->displayName,
            'work_email' => $data->workEmail,
            'personal_email' => $data->personalEmail,
            'phone' => $data->phone,
            'hired_at' => $data->hiredAt,
            'ended_at' => $data->endedAt,
            'notes' => $data->notes,
            'active' => $data->active,
        ];
    }

    private function syncAvatar(Employee $employee, EmployeeData $data): void
    {
        if ($data->removeAvatar) {
            $employee->clearMediaCollection('avatar');
        }

        if (! $data->avatar) {
            return;
        }

        $employee
            ->addMedia($data->avatar)
            ->usingName($employee->display_name)
            ->toMediaCollection('avatar');
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $user) => [
                    'value' => $user->id,
                    'label' => "{$user->name} ({$user->email})",
                ])
                ->values()
                ->all(),
            'departements' => $this->modelOptions(Departement::query()->where('active', true)),
            'positions' => $this->modelOptions(Position::query()->where('active', true)),
            'jobLevels' => $this->modelOptions(JobLevel::query()->where('active', true)),
            'workLocations' => $this->modelOptions(WorkLocation::query()->where('active', true)),
            'employmentStatuses' => $this->modelOptions(EmploymentStatus::query()->where('active', true)),
            'employmentTypes' => $this->modelOptions(EmploymentType::query()->where('active', true)),
        ];
    }

    /**
     * @param  Builder<Model>  $query
     * @return array<int, array{value: int, label: string}>
     */
    private function modelOptions(Builder $query): array
    {
        return $query
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Model $model) => [
                'value' => $model->id,
                'label' => "{$model->name} ({$model->code})",
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function resolvePerPage(array $filters): int
    {
        $settings = $this->settings->paginationSettings();
        $default = (int) $settings['default_per_page'];
        $options = $settings['per_page_options'];
        $perPage = (int) ($filters['per_page'] ?? $default);

        return in_array($perPage, $options, true) ? $perPage : $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPaginator(LengthAwarePaginator $employees): array
    {
        return [
            'data' => collect($employees->items())
                ->map(fn (Employee $employee) => $this->transformEmployee($employee))
                ->values()
                ->all(),
            'current_page' => $employees->currentPage(),
            'last_page' => $employees->lastPage(),
            'per_page' => $employees->perPage(),
            'total' => $employees->total(),
            'from' => $employees->firstItem(),
            'to' => $employees->lastItem(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformEmployee(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'user_id' => $employee->user_id,
            'departement_id' => $employee->departement_id,
            'position_id' => $employee->position_id,
            'job_level_id' => $employee->job_level_id,
            'work_location_id' => $employee->work_location_id,
            'employment_status_id' => $employee->employment_status_id,
            'employment_type_id' => $employee->employment_type_id,
            'employee_number' => $employee->employee_number,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'display_name' => $employee->display_name,
            'work_email' => $employee->work_email,
            'personal_email' => $employee->personal_email,
            'phone' => $employee->phone,
            'hired_at' => $employee->hired_at?->toDateString(),
            'ended_at' => $employee->ended_at?->toDateString(),
            'notes' => $employee->notes,
            'active' => $employee->active,
            'avatar' => $employee->avatar,
            'user' => $employee->user ? ['id' => $employee->user->id, 'name' => $employee->user->name, 'email' => $employee->user->email] : null,
            'departement' => $this->relatedLabel($employee->departement),
            'position' => $this->relatedLabel($employee->position),
            'job_level' => $this->relatedLabel($employee->jobLevel),
            'work_location' => $this->relatedLabel($employee->workLocation),
            'employment_status' => $this->relatedLabel($employee->employmentStatus),
            'employment_type' => $this->relatedLabel($employee->employmentType),
            'deleted_at' => $employee->deleted_at?->toISOString(),
            'created_at' => $employee->created_at?->toISOString(),
            'updated_at' => $employee->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array{id: int, code: string, name: string}|null
     */
    private function relatedLabel(?Model $model): ?array
    {
        if (! $model) {
            return null;
        }

        return [
            'id' => $model->id,
            'code' => (string) $model->getAttribute('code'),
            'name' => (string) $model->getAttribute('name'),
        ];
    }
}
