<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingTemplateArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['offboardings.view', 'offboardings.template-manage'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_archives_and_restores_template_without_losing_items(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('offboardings.template-manage');
        $template = $this->template();

        $this->actingAs($user)
            ->delete(route('hr.offboardings.templates.archive', $template))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_offboarding_templates', ['id' => $template->id]);
        $this->assertDatabaseHas('hr_offboarding_template_items', [
            'offboarding_template_id' => $template->id,
            'title' => 'Handover',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'hr.offboardings',
            'event' => 'OffboardingTemplate.archived',
            'auditable_id' => $template->id,
        ]);

        $this->actingAs($user)
            ->patch(route('hr.offboardings.templates.restore', $template))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_offboarding_templates', [
            'id' => $template->id,
            'deleted_at' => null,
        ]);
        $this->assertSame(1, $template->fresh()->items()->count());
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'hr.offboardings',
            'event' => 'OffboardingTemplate.restored',
            'auditable_id' => $template->id,
        ]);
    }

    public function test_default_list_excludes_archived_and_explicit_filter_includes_history(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $active = $this->template('ACTIVE');
        $archived = $this->template('ARCHIVED');
        $archived->delete();

        $this->actingAs($viewer)
            ->get(route('hr.offboardings.templates.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('templates.data', 1)
                ->where('templates.data.0.id', $active->id)
                ->where('showArchived', false));

        $this->actingAs($viewer)
            ->get(route('hr.offboardings.templates.index', ['archived' => 1]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('templates.data', 2)
                ->where('showArchived', true)
                ->where('templates.data.1.id', $archived->id)
                ->where('templates.data.1.archived', true));
    }

    public function test_archived_code_remains_reserved_and_restore_is_idempotent(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('offboardings.template-manage');
        $template = $this->template('RESERVED');
        $template->delete();

        $this->actingAs($user)->post(route('hr.offboardings.templates.store'), [
            'code' => 'reserved',
            'name' => 'Duplicate',
            'items' => [
                [
                    'title' => 'Handover',
                    'category' => 'HR',
                    'required' => true,
                    'due_offset_days' => -7,
                ],
            ],
        ])->assertSessionHasErrors('code');

        $this->actingAs($user)
            ->patch(route('hr.offboardings.templates.restore', $template))
            ->assertRedirect();
        $this->actingAs($user)
            ->patch(route('hr.offboardings.templates.restore', $template))
            ->assertNotFound();

        $this->assertDatabaseCount('hr_offboarding_templates', 1);
    }

    public function test_unauthorized_mutations_are_denied_and_force_delete_route_is_absent(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $template = $this->template();

        $this->actingAs($viewer)
            ->delete(route('hr.offboardings.templates.archive', $template))
            ->assertForbidden();

        $template->delete();
        $this->actingAs($viewer)
            ->patch(route('hr.offboardings.templates.restore', $template))
            ->assertForbidden();

        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('hr.offboardings.templates.force-delete'));
        $this->assertSoftDeleted('hr_offboarding_templates', ['id' => $template->id]);
        $this->assertDatabaseCount('hr_offboarding_template_items', 1);
    }

    private function template(string $code = 'STANDARD'): OffboardingTemplate
    {
        $template = OffboardingTemplate::query()->create([
            'code' => $code,
            'name' => ucfirst(strtolower($code)),
            'active' => true,
        ]);
        $template->items()->create([
            'title' => 'Handover',
            'category' => 'OPERATIONS',
            'required' => true,
            'due_offset_days' => -7,
            'sort_order' => 0,
        ]);

        return $template;
    }
}
