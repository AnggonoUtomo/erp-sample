<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['onboardings.view', 'onboardings.archive', 'onboardings.restore'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_list_filters_are_deterministic_and_paginated(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('onboardings.view');
        $target = $this->onboarding('IN_PROGRESS', '2026-07-01', $viewer);
        $target->tasks()->create(['title' => 'Overdue', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'due_date' => '2026-07-09', 'sort_order' => 1, 'status' => 'PENDING']);
        $other = $this->onboarding('COMPLETED', '2026-06-01');
        $other->tasks()->create(['title' => 'Old optional', 'category' => 'HR', 'required' => false, 'due_offset_days' => 0, 'due_date' => '2026-06-02', 'sort_order' => 1, 'status' => 'PENDING']);

        $this->actingAs($viewer)->get(route('hr.onboardings.index', [
            'employee_id' => $target->employee_id,
            'owner_user_id' => $viewer->id,
            'status' => 'IN_PROGRESS',
            'template_id' => $target->onboarding_template_id,
            'start_from' => '2026-07-01',
            'start_to' => '2026-07-01',
            'overdue' => '1',
            'business_date' => '2026-07-10',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('filters.overdue', true)
            ->where('businessDate', '2026-07-10')
            ->has('onboardings.data', 1)
            ->where('onboardings.data.0.id', $target->id));
    }

    public function test_terminal_onboarding_can_be_archived_and_restored_without_force_delete(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo(['onboardings.archive', 'onboardings.restore']);
        $terminal = $this->onboarding('COMPLETED', '2026-07-01');
        $active = $this->onboarding('IN_PROGRESS', '2026-07-02');

        $this->actingAs($actor)->delete(route('hr.onboardings.archive', $active))->assertSessionHasErrors('status');
        $this->actingAs($actor)->delete(route('hr.onboardings.archive', $terminal))->assertRedirect();
        $this->assertSoftDeleted('hr_onboardings', ['id' => $terminal->id]);
        $this->actingAs($actor)->patch(route('hr.onboardings.restore', $terminal->id))->assertRedirect();

        $this->assertDatabaseHas('hr_onboardings', ['id' => $terminal->id, 'deleted_at' => null, 'active_identity_key' => null]);
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('hr.onboardings.force-delete'));
        $this->assertDatabaseHas('audit_logs', ['event' => 'Onboarding.archived', 'auditable_id' => $terminal->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Onboarding.restored', 'auditable_id' => $terminal->id]);
    }

    public function test_archive_and_restore_are_permission_protected(): void
    {
        $actor = User::factory()->create();
        $terminal = $this->onboarding('CANCELLED', '2026-07-01');

        $this->actingAs($actor)->delete(route('hr.onboardings.archive', $terminal))->assertForbidden();
        $terminal->delete();
        $this->actingAs($actor)->patch(route('hr.onboardings.restore', $terminal->id))->assertForbidden();
    }

    private function onboarding(string $status, string $startDate, ?User $owner = null): Onboarding
    {
        $employee = Employee::query()->create(['employee_number' => 'EMP-'.uniqid(), 'first_name' => 'Ops', 'display_name' => 'Ops User', 'active' => true]);
        $template = OnboardingTemplate::query()->create(['code' => 'OPS-'.uniqid(), 'name' => 'Operations', 'active' => true]);
        $active = in_array($status, ['DRAFT', 'IN_PROGRESS'], true);

        return Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => ($owner ?? User::factory()->create())->id,
            'start_date' => $startDate,
            'status' => $status,
            'active_identity_key' => $active ? "employee:{$employee->id}:start:{$startDate}" : null,
            'request_fingerprint' => hash('sha256', "ops-{$employee->id}"),
        ]);
    }
}
