<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['hr.view', 'onboardings.view', 'onboardings.template-manage'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_can_create_template_with_ordered_items_atomically(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('onboardings.template-manage');

        $this->actingAs($user)->post(route('hr.onboardings.templates.store'), [
            'code' => 'NEW-HIRE',
            'name' => 'Karyawan Baru',
            'description' => 'Checklist standar.',
            'active' => true,
            'items' => [
                ['title' => 'Buat akun', 'category' => 'IT', 'required' => true, 'due_offset_days' => -2],
                ['title' => 'Orientasi HR', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0],
            ],
        ])->assertRedirect(route('hr.onboardings.templates.index'));

        $template = OnboardingTemplate::query()->where('code', 'NEW-HIRE')->firstOrFail();
        $this->assertSame(['Buat akun', 'Orientasi HR'], $template->items()->orderBy('sort_order')->pluck('title')->all());
        $this->assertSame([0, 1], $template->items()->orderBy('sort_order')->pluck('sort_order')->all());
    }

    public function test_invalid_or_duplicate_input_creates_no_partial_items(): void
    {
        OnboardingTemplate::query()->create(['code' => 'EXISTING', 'name' => 'Existing', 'active' => true]);
        $user = User::factory()->create();
        $user->givePermissionTo('onboardings.template-manage');

        $this->actingAs($user)->post(route('hr.onboardings.templates.store'), [
            'code' => 'existing',
            'name' => 'Duplicate',
            'items' => [
                ['title' => '', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0],
            ],
        ])->assertSessionHasErrors(['code', 'items.0.title']);

        $this->assertDatabaseCount('hr_onboarding_templates', 1);
        $this->assertDatabaseCount('hr_onboarding_template_items', 0);
    }

    public function test_authorized_user_can_list_templates_and_unauthorized_users_are_denied(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('onboardings.view');
        $unauthorized = User::factory()->create();

        $this->actingAs($viewer)->get(route('hr.onboardings.templates.index'))->assertOk();
        $this->actingAs($unauthorized)->get(route('hr.onboardings.templates.index'))->assertForbidden();
        $this->actingAs($unauthorized)->post(route('hr.onboardings.templates.store'))->assertForbidden();
        auth()->logout();
        $this->post(route('hr.onboardings.templates.store'))->assertRedirect(route('hr.login'));
    }
}
