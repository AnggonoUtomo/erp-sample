<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingTemplateArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['onboardings.view', 'onboardings.template-manage', 'onboardings.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_can_archive_and_restore_template_without_losing_items(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('onboardings.template-manage');
        $template = $this->template();

        $this->actingAs($user)->delete(route('hr.onboardings.templates.archive', $template))->assertRedirect();

        $this->assertSoftDeleted('hr_onboarding_templates', ['id' => $template->id]);
        $this->assertDatabaseHas('hr_onboarding_template_items', ['onboarding_template_id' => $template->id, 'title' => 'Orientasi']);
        $this->assertFalse(OnboardingTemplate::availableForOnboarding()->whereKey($template->id)->exists());

        $this->actingAs($user)->patch(route('hr.onboardings.templates.restore', $template->id))->assertRedirect();

        $this->assertDatabaseHas('hr_onboarding_templates', ['id' => $template->id, 'deleted_at' => null]);
        $this->assertSame('Orientasi', OnboardingTemplate::query()->findOrFail($template->id)->items()->firstOrFail()->title);
    }

    public function test_unauthorized_mutations_are_denied_and_no_force_delete_route_exists(): void
    {
        $user = User::factory()->create();
        $template = $this->template();

        $this->actingAs($user)->delete(route('hr.onboardings.templates.archive', $template))->assertForbidden();
        $this->actingAs($user)->patch(route('hr.onboardings.templates.restore', $template->id))->assertForbidden();
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('hr.onboardings.templates.force-delete'));
        $this->assertDatabaseHas('hr_onboarding_templates', ['id' => $template->id, 'deleted_at' => null]);
    }

    private function template(): OnboardingTemplate
    {
        $template = OnboardingTemplate::query()->create(['code' => 'ARCHIVE-ME', 'name' => 'Archive Me', 'active' => true]);
        $template->items()->create(['title' => 'Orientasi', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'sort_order' => 0]);

        return $template;
    }
}
