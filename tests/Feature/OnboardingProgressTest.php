<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('onboardings.view');
    }

    public function test_detail_returns_ordered_tasks_and_deterministic_progress_summary(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('onboardings.view');
        $onboarding = $this->onboarding();
        $onboarding->tasks()->createMany([
            $this->task('Optional selesai', false, 3, '2026-07-10', OnboardingTaskStatus::Completed),
            $this->task('Required terlambat', true, 1, '2026-07-14', OnboardingTaskStatus::Pending),
            $this->task('Optional mendatang', false, 2, '2026-07-20', OnboardingTaskStatus::InProgress),
            $this->task('Required selesai', true, 0, '2026-07-12', OnboardingTaskStatus::Skipped),
        ]);

        $this->actingAs($viewer)
            ->get(route('hr.onboardings.show', ['onboarding' => $onboarding, 'business_date' => '2026-07-15']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/onboardings/show')
                ->where('businessDate', '2026-07-15')
                ->where('onboarding.progress.total', 4)
                ->where('onboarding.progress.terminal', 2)
                ->where('onboarding.progress.completed', 1)
                ->where('onboarding.progress.required_incomplete', 1)
                ->where('onboarding.progress.optional_incomplete', 1)
                ->where('onboarding.progress.overdue', 1)
                ->where('onboarding.progress.percentage', 50)
                ->where('onboarding.tasks.0.title', 'Required selesai')
                ->where('onboarding.tasks.1.title', 'Required terlambat')
                ->where('onboarding.tasks.2.title', 'Optional mendatang')
                ->where('onboarding.tasks.3.title', 'Optional selesai')
                ->has('assigneeOptions', 0)
                ->missing('onboarding.active_identity_key')
                ->missing('onboarding.request_fingerprint')
                ->missing('onboarding.tasks.0.source_template_item_id'));
    }

    public function test_archived_detail_is_readable_and_empty_state_is_explicit(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('onboardings.view');
        $onboarding = $this->onboarding();
        $onboarding->delete();

        $this->actingAs($viewer)
            ->get(route('hr.onboardings.show', ['onboarding' => $onboarding->id, 'business_date' => '2026-07-15']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('onboarding.archived', true)
                ->where('onboarding.progress.total', 0)
                ->where('onboarding.progress.percentage', 0)
                ->has('onboarding.tasks', 0));
    }

    public function test_detail_is_permission_protected_and_rejects_invalid_business_date(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('onboardings.view');
        $unauthorized = User::factory()->create();
        $onboarding = $this->onboarding();

        $this->actingAs($unauthorized)
            ->get(route('hr.onboardings.show', ['onboarding' => $onboarding, 'business_date' => '2026-07-15']))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->from(route('hr.onboardings.index'))
            ->get(route('hr.onboardings.show', ['onboarding' => $onboarding, 'business_date' => 'not-a-date']))
            ->assertRedirect(route('hr.onboardings.index'))
            ->assertSessionHasErrors('business_date');
    }

    private function onboarding(): Onboarding
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-PROGRESS',
            'first_name' => 'Uji',
            'display_name' => 'Uji Progress',
            'active' => true,
        ]);
        $owner = User::factory()->create(['name' => 'Onboarding Owner']);
        $template = OnboardingTemplate::query()->create(['code' => 'PROGRESS', 'name' => 'Progress Template', 'active' => true]);

        return Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-15',
            'status' => 'DRAFT',
        ]);
    }

    /** @return array<string, mixed> */
    private function task(string $title, bool $required, int $sortOrder, string $dueDate, OnboardingTaskStatus $status): array
    {
        return [
            'title' => $title,
            'category' => 'HR',
            'required' => $required,
            'due_offset_days' => 0,
            'due_date' => $dueDate,
            'sort_order' => $sortOrder,
            'status' => $status,
        ];
    }
}
