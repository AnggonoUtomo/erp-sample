<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\Console\LoginActivities\Models\LoginActivity;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use App\Support\Modules\ModulePermissionRegistry;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $canViewConsoleAdmin = $user?->hasAnyPermission(['access-control.view', 'roles.manage', 'users.view']) ?? false;
        $canViewHr = $user?->can('hr.view') ?? false;
        $canViewDms = $user?->hasAnyPermission(['document-management.view', 'documents.view']) ?? false;
        $canViewAudit = $user?->can('audit-logs.view') ?? false;
        $canViewLoginActivities = $user?->can('login-activities.view') ?? false;

        return Inertia::render('console/dashboard', [
            'dashboard' => [
                'access' => [
                    'console_admin' => $canViewConsoleAdmin,
                    'hr' => $canViewHr,
                    'dms' => $canViewDms,
                    'audit' => $canViewAudit,
                    'login_activities' => $canViewLoginActivities,
                ],
                'console' => $this->consoleMetrics($canViewConsoleAdmin, count($user?->getUserPermissions() ?? [])),
                'hr' => $canViewHr ? $this->hrMetrics() : $this->emptyHrMetrics(),
                'dms' => $canViewDms ? $this->documentManagementMetrics() : $this->emptyDocumentManagementMetrics(),
                'activity' => $this->activityMetrics($canViewAudit, $canViewLoginActivities),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function consoleMetrics(bool $canViewConsoleAdmin, int $effectivePermissionCount): array
    {
        $modules = ModuleRegistry::modules();

        return [
            'modules' => $modules->count(),
            'console_modules' => $modules->where('project', 'Console')->count(),
            'hr_modules' => $modules->where('project', 'HR')->count(),
            'permissions' => $canViewConsoleAdmin ? count(ModulePermissionRegistry::permissions()) : $effectivePermissionCount,
            'roles' => $canViewConsoleAdmin ? Role::query()->count() : 0,
            'users' => $canViewConsoleAdmin ? User::query()->count() : 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function hrMetrics(): array
    {
        return [
            'employees' => Employee::query()->count(),
            'active_employees' => Employee::query()->where('active', true)->count(),
            'departements' => Departement::query()->count(),
            'positions' => Position::query()->count(),
            'job_levels' => JobLevel::query()->count(),
            'work_locations' => WorkLocation::query()->count(),
            'employment_statuses' => EmploymentStatus::query()->count(),
            'employment_types' => EmploymentType::query()->count(),
            'reference_data' => ReferenceData::query()->count(),
            'organization_nodes' => OrganizationStructure::query()->count(),
            'contracts' => EmployeeContract::query()->count(),
            'documents' => EmployeeDocument::query()->count(),
            'movements' => EmployeeMovement::query()->count(),
            'onboardings' => Onboarding::query()->count(),
            'offboardings' => Offboarding::query()->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyHrMetrics(): array
    {
        return [
            'employees' => 0,
            'active_employees' => 0,
            'departements' => 0,
            'positions' => 0,
            'job_levels' => 0,
            'work_locations' => 0,
            'employment_statuses' => 0,
            'employment_types' => 0,
            'reference_data' => 0,
            'organization_nodes' => 0,
            'contracts' => 0,
            'documents' => 0,
            'movements' => 0,
            'onboardings' => 0,
            'offboardings' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function documentManagementMetrics(): array
    {
        return [
            'documents' => LogicalDocument::query()->count(),
            'versions' => DocumentVersion::query()->count(),
            'available_versions' => DocumentVersion::query()->where('status', 'AVAILABLE')->count(),
            'quarantined_versions' => DocumentVersion::query()->where('status', 'QUARANTINED')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyDocumentManagementMetrics(): array
    {
        return [
            'documents' => 0,
            'versions' => 0,
            'available_versions' => 0,
            'quarantined_versions' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function activityMetrics(bool $canViewAudit, bool $canViewLoginActivities): array
    {
        return [
            'audit_logs' => $canViewAudit ? AuditLog::query()->count() : 0,
            'login_activities' => $canViewLoginActivities ? LoginActivity::query()->count() : 0,
            'recent_audit_logs' => $canViewAudit ? AuditLog::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'module', 'event', 'description', 'created_at'])
                ->map(fn (AuditLog $log): array => [
                    'id' => $log->id,
                    'module' => $log->module,
                    'event' => $log->event,
                    'description' => $log->description,
                    'created_at_human' => $log->created_at?->diffForHumans(),
                ])
                ->all() : [],
            'recent_login_activities' => $canViewLoginActivities ? LoginActivity::query()
                ->latest('occurred_at')
                ->limit(3)
                ->get(['id', 'email', 'event', 'successful', 'occurred_at'])
                ->map(fn (LoginActivity $activity): array => [
                    'id' => $activity->id,
                    'email' => $activity->email,
                    'event' => $activity->event,
                    'successful' => $activity->successful,
                    'occurred_at_human' => $activity->occurred_at?->diffForHumans(),
                ])
                ->all() : [],
        ];
    }
}
